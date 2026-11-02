<?php

namespace Modules\ReelsModule\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Modules\ReelsModule\Http\Requests\Api\V1\ReelLikeRequest;
use Modules\ReelsModule\Http\Requests\Api\V1\ReelListRequest;
use Modules\ReelsModule\Http\Requests\Api\V1\ReelShowRequest;
use Modules\ReelsModule\Http\Requests\Api\V1\ReelStatsRequest;
use Modules\ReelsModule\Http\Requests\Api\V1\ReelVisitRequest;
use Modules\ReelsModule\Http\Resources\ReelDetailResource;
use Modules\ReelsModule\Http\Resources\ReelListResource;
use Modules\ReelsModule\Http\Resources\ReelStatsResource;
use Modules\ReelsModule\Services\ReelApiService;

class ReelController extends Controller
{
    public function __construct(private readonly ReelApiService $reelApiService)
    {
    }

    public function index(ReelListRequest $request): JsonResponse
    {
        $offset = (int) ($request->input('offset', $request->input('page', 1)) ?: 1);
        $limit = (int) ($request->input('limit', 10));
        $reels = $this->reelApiService->getReels(
            restaurantId: $request->filled('restaurant_id') ? (int) $request->input('restaurant_id') : null,
            limit: $limit,
            page: $offset,
            zoneId: $request->header('zoneId')
        );

        return response()->json([
            'total_size' => (int) $reels->total(),
            'limit' => $limit,
            'offset' => $offset,
            'reels' => ReelListResource::collection($reels->items())->resolve(),
        ]);
    }

    public function show(ReelShowRequest $request)
    {
        $reel = $this->reelApiService->getReelDetails((int) $request->input('reel_id'));

        if (!$reel) {
            return response()->json(['message' => translate('messages.not_found')], 404);
        }

        $user = $request->user('api');
        $userId = $user ? $user->id : null;
        $guestId = $userId ? null : $request->input('guest_id');

        $this->reelApiService->trackView($reel, $userId, $guestId);

        if ($request->boolean('stream') || $request->headers->has('Range')) {
            return $this->reelApiService->streamVideo($reel, $request);
        }

        return response()->json((new ReelDetailResource($reel->fresh([
            'restaurant.restaurant_config:id,restaurant_id,verified_seller',
            'storage',
            'activeFood',
        ])))->resolve(), 200);
    }

    public function stats(ReelStatsRequest $request): JsonResponse
    {
        $reel = $this->reelApiService->getReelDetails((int) $request->input('reel_id'));

        if (!$reel) {
            return response()->json(['message' => translate('messages.not_found')], 404);
        }

        return response()->json((new ReelStatsResource($reel))->resolve(), 200);
    }

    public function like(ReelLikeRequest $request): JsonResponse
    {
        $reel = $this->reelApiService->getReelDetails((int) $request->input('reel_id'));

        if (!$reel) {
            return response()->json(['message' => translate('messages.not_found')], 404);
        }

        $result = $this->reelApiService->toggleLike($reel, (int) $request->user('api')->id);

        return response()->json([
            'liked' => $result['liked'],
            'total_likes' => $result['total_likes'],
            'message' => $result['message'],
        ]);
    }

    public function visit(ReelVisitRequest $request): JsonResponse
    {
        $reel = $this->reelApiService->getReelDetails((int) $request->input('reel_id'));

        if (!$reel) {
            return response()->json(['message' => translate('messages.not_found')], 404);
        }

        $user = $request->user('api');
        $userId = $user ? $user->id : null;
        $guestId = $userId ? null : $request->input('guest_id');

        $this->reelApiService->trackVisit($reel, $userId, $guestId);

        return response()->json([
            'total_store_visits' => (int) $reel->fresh()->total_store_visits,
            'message' => translate('messages.successfully_updated'),
        ]);
    }

}
