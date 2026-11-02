<?php

namespace Modules\ReelsModule\Services;

use App\CentralLogics\Helpers;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\ReelsModule\Entities\Reel;
use Modules\ReelsModule\Entities\ReelEngagement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReelApiService
{
    public function getReels(?int $restaurantId, int $limit, int $page, ?string $zoneId = null): LengthAwarePaginator
    {
        $zoneIds = $this->resolveZoneIds($zoneId);

        return Reel::query()
            ->active()
            ->with([
                'restaurant.restaurant_config:id,restaurant_id,verified_seller',
                'storage',
                'engagements',
                'activeFood',
            ])
            ->select([
                'id',
                'description',
                'thumbnail',
                'video',
                'restaurant_id',
                'status',
                'is_always_visible',
                'start_date',
                'end_date',
                'total_views',
                'total_likes',
                'total_store_visits',
                'food_id',
                'order_now_button'
            ])
            ->withCount([
                'engagements as total_sale' => fn (Builder $builder) => $builder->where('type', ReelEngagement::TYPE_ORDER),
            ])
            ->withSum([
                'engagements as total_sale_amount' => fn (Builder $builder) => $builder->where('type', ReelEngagement::TYPE_ORDER),
            ], 'amount')
            ->when($restaurantId, fn (Builder $query) => $query->where('restaurant_id', $restaurantId))
            ->when(!empty($zoneIds), function (Builder $query) use ($zoneIds) {
                $query->whereHas('restaurant', function (Builder $restaurantQuery) use ($zoneIds) {
                    $restaurantQuery->when(!empty($zoneIds), function ($q) use ($zoneIds) {
                        $q->whereIn('zone_id', $zoneIds);
                    });
                });
            })
            ->latest('id')
            ->paginate($limit, ['*'], 'page', $page);
    }

    private function resolveZoneIds(?string $zoneId): array
    {
        if (empty($zoneId)) {
            return [];
        }

        $decoded = json_decode($zoneId, true);
        if (is_array($decoded)) {
            return array_values(array_filter($decoded, fn ($value) => is_numeric($value)));
        }

        return is_numeric($zoneId) ? [(int) $zoneId] : [];
    }

    public function getReelDetails(int $id): ?Reel
    {
        return Reel::query()
            ->active()
            ->with([
                'restaurant.restaurant_config:id,restaurant_id,verified_seller',
                'storage',
                'engagements',
                'activeFood',
            ])
            ->withCount([
                'engagements as total_sale' => fn (Builder $builder) => $builder->where('type', ReelEngagement::TYPE_ORDER),
            ])
            ->withSum([
                'engagements as total_sale_amount' => fn (Builder $builder) => $builder->where('type', ReelEngagement::TYPE_ORDER),
            ], 'amount')
            ->find($id);
    }

    public function trackView(Reel $reel, ?int $userId, ?string $guestId): void
    {
        $this->trackUniqueEngagement($reel, $userId, $guestId, 'view', 'total_views');
    }

    public function trackVisit(Reel $reel, ?int $userId, ?string $guestId): void
    {
        $this->trackUniqueEngagement($reel, $userId, $guestId, 'visit', 'total_store_visits');
    }

    public function trackOrder(Reel $reel, ?int $userId, ?string $guestId, float $amount = 0): void
    {
        $this->recordOrderSale($reel->id, $amount, $userId, $guestId);
    }

    public function recordOrderSale(int $reelId, float $amount = 0, ?int $userId = null, ?string $guestId = null): bool
    {
            $reel = Reel::query()->lockForUpdate()->find($reelId);

            if (!$reel) {
                return false;
            }

            ReelEngagement::create([
                'reel_id' => $reel->id,
                'user_id' => $userId,
                'guest_id' => $userId ? null : $guestId,
                'type' => ReelEngagement::TYPE_ORDER,
                'amount' => $amount,
            ]);

            $reel->increment('order_count');
            $reel->increment('total_sale_amount', $amount);
            return true;

    }

    public function toggleLike(Reel $reel, int $userId): array
    {
        return DB::transaction(function () use ($reel, $userId) {
            $engagementQuery = ReelEngagement::query()
                ->where('reel_id', $reel->id)
                ->where('user_id', $userId)
                ->where('type', 'like');

            $existingEngagement = $engagementQuery->lockForUpdate()->first();
            $lockedReel = Reel::query()->lockForUpdate()->findOrFail($reel->id);

            if ($existingEngagement) {
                $existingEngagement->delete();
                if ($lockedReel->total_likes > 0) {
                    $lockedReel->decrement('total_likes');
                }

                return [
                    'liked' => false,
                    'total_likes' => max(0, (int) $lockedReel->fresh()->total_likes),
                    'message' => 'Unliked successfully',
                ];
            }

            ReelEngagement::create([
                'reel_id' => $lockedReel->id,
                'user_id' => $userId,
                'type' => 'like',
            ]);

            $lockedReel->increment('total_likes');

            return [
                'liked' => true,
                'total_likes' => (int) $lockedReel->fresh()->total_likes,
                'message' => 'Liked successfully',
            ];
        });
    }

    public function streamVideo(Reel $reel, Request $request): Response|StreamedResponse|RedirectResponse|JsonResponse
    {
        $disk         = $this->resolveVideoDisk($reel);
        $relativePath = 'reels/' . $reel->video;

        // Resolve local path in one step; fall back to redirect for remote disks (S3, etc.)
        $filePath = method_exists(Storage::disk($disk), 'path')
            ? Storage::disk($disk)->path($relativePath)
            : null;

        if (!$filePath || !is_file($filePath)) {
            // The object still exists on the disk but can't be streamed locally
            // (remote/CDN disk where path() is unavailable) — hand off to its URL.
            if ($reel->video && Storage::disk($disk)->exists($relativePath)) {
                return response()->redirectTo($reel->video_full_url);
            }

            // File is genuinely missing — return a clean JSON 404 instead of a
            // raw HttpException stack trace.
            return response()->json(['message' => translate('messages.reel_video_not_found')], 404);
        }

        $fileSize     = filesize($filePath);
        $mtime        = filemtime($filePath);
        $etag         = '"' . md5($mtime . $fileSize) . '"';
        $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        // Conditional request — return 304 if client already has current version
        if ($request->header('If-None-Match') === $etag ||
            ($request->header('If-Modified-Since') && strtotime($request->header('If-Modified-Since')) >= $mtime)
        ) {
            return response('', 304, [
                'ETag'          => $etag,
                'Last-Modified' => $lastModified,
            ]);
        }

        // Derive MIME type from extension to avoid reading magic bytes on every request
        static $mimeMap = [
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'mov'  => 'video/quicktime',
            'mkv'  => 'video/x-matroska',
            '3gp'  => 'video/3gpp',
            'gif'  => 'image/gif',
        ];
        $ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeMap[$ext] ?? (mime_content_type($filePath) ?: 'video/mp4');

        $start  = 0;
        $end    = $fileSize - 1;
        $status = 200;
        $headers = [
            'Content-Type'  => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=86400',
            'ETag'          => $etag,
            'Last-Modified' => $lastModified,
        ];

        $rangeHeader = $request->header('Range');
        if ($rangeHeader && preg_match('/bytes=(\d*)-(\d*)/i', $rangeHeader, $matches)) {
            $start = $matches[1] !== '' ? (int) $matches[1] : 0;
            $end   = $matches[2] !== '' ? (int) $matches[2] : $end;
            $end   = min($end, $fileSize - 1);

            if ($start > $end || $start >= $fileSize) {
                return response('', 416, [
                    'Content-Range' => "bytes */{$fileSize}",
                ]);
            }

            $status                  = 206;
            $headers['Content-Range'] = "bytes {$start}-{$end}/{$fileSize}";
        }

        $headers['Content-Length'] = (string) ($end - $start + 1);

        return response()->stream(function () use ($filePath, $start, $end) {
            // Discard any buffered output from middleware/session before streaming
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            $chunkSize = 1024 * 1024;
            $handle    = fopen($filePath, 'rb');

            if ($handle === false) {
                return;
            }

            try {
                fseek($handle, $start);
                $bytesRemaining = $end - $start + 1;

                while (!feof($handle) && $bytesRemaining > 0) {
                    if (connection_aborted()) {
                        break;
                    }

                    $readLength = min($chunkSize, $bytesRemaining);
                    $buffer     = fread($handle, $readLength);

                    if ($buffer === false) {
                        break;
                    }

                    echo $buffer;
                    flush();

                    $bytesRemaining -= strlen($buffer);
                }
            } finally {
                fclose($handle);
            }
        }, $status, $headers);
    }

    private function trackUniqueEngagement(Reel $reel, ?int $userId, ?string $guestId, string $type, ?string $counterColumn, float $amount = 0): void
    {
        DB::transaction(function () use ($reel, $userId, $guestId, $type, $counterColumn, $amount) {
            $engagementQuery = ReelEngagement::query()
                ->where('reel_id', $reel->id)
                ->where('type', $type)
                ->when($userId, fn ($query) => $query->where('user_id', $userId))
                ->when(!$userId, fn ($query) => $query->where('guest_id', $guestId));

            $alreadyTracked = $engagementQuery->lockForUpdate()->exists();

            if ($alreadyTracked) {
                return;
            }

            ReelEngagement::create([
                'reel_id' => $reel->id,
                'user_id' => $userId,
                'guest_id' => $userId ? null : $guestId,
                'type' => $type,
                'amount' => $amount > 0 ? $amount : null,
            ]);

            if ($counterColumn) {
                Reel::query()->where('id', $reel->id)->increment($counterColumn);
            }
        });
    }

    private function resolveVideoDisk(Reel $reel): string
    {
        foreach ($reel->storage as $storage) {
            if ($storage->key === 'video' && $storage->value) {
                return $storage->value;
            }
        }

        return Helpers::getDisk();
    }
}
