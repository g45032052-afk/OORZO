<?php

namespace App\Builder;

use App\CentralLogics\Helpers;
use App\CentralLogics\ProductLogic;
use App\CentralLogics\RestaurantLogic;
use App\Models\DeliveryMan;
use App\Models\DMReview;
use App\Models\Food;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Builder\Contracts\ReviewProvider as ReviewProviderContract;
use Modules\Builder\ValueObjects\StorefrontScope;

/**
 * Customer review submission against delivered orders.
 *
 * Mirrors the host's Api\V1 item + delivery-man review submission (same
 * Review / DMReview models + denorm chain). StackFood is food-only: reviews
 * key on `food_id`, scope on `restaurant_id`, the restaurant rating bucket is
 * updated via RestaurantLogic (not StoreLogic), and there is no `module_id`.
 * StackFood's Review has no slug column, so the review row `id` is returned.
 */
class ReviewProvider implements ReviewProviderContract
{
    public function reviewContext(?StorefrontScope $scope, int $orderId, int $customerId): ?array
    {
        $order = $this->loadOrder($scope, $orderId, $customerId);
        if (!$order) {
            return null;
        }

        $reviewedItemIds = Review::query()
            ->where('user_id', $customerId)
            ->where('order_id', $orderId)
            ->pluck('food_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $reviewSlugs = Review::query()
            ->where('user_id', $customerId)
            ->where('order_id', $orderId)
            ->pluck('id', 'food_id')
            ->all();

        // Dedupe details by food_id — an order can carry multiple detail rows
        // for the same food (different variants). Review uniqueness is
        // (food_id, user_id, order_id), so one review covers all variants.
        $itemsById = [];
        foreach ($order->details as $detail) {
            $itemId = (int) ($detail->food_id ?? 0);
            if ($itemId <= 0 || isset($itemsById[$itemId])) {
                continue;
            }
            $itemsById[$itemId] = $this->mapItemForReview($detail, $reviewedItemIds, $reviewSlugs);
        }
        $items = array_values($itemsById);

        $deliveryMan = null;
        if ($order->delivery_man) {
            $dmReviewed = DMReview::query()
                ->where('user_id', $customerId)
                ->where('order_id', $orderId)
                ->where('delivery_man_id', $order->delivery_man->id)
                ->exists();
            $deliveryMan = $this->mapDeliveryManForReview($order->delivery_man, $dmReviewed);
        }

        $allItemsReviewed = collect($items)->every(fn ($i) => $i['reviewed']);
        $dmDone = $deliveryMan === null || $deliveryMan['reviewed'];

        return [
            'orderId'     => (int) $order->id,
            'items'       => $items,
            'deliveryMan' => $deliveryMan,
            'allReviewed' => $allItemsReviewed && $dmDone,
        ];
    }

    public function submitItemReview(
        ?StorefrontScope $scope,
        int $orderId,
        int $customerId,
        int $itemId,
        int $rating,
        ?string $comment,
        array $imageFiles = [],
    ): array {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'error' => 'Rating must be between 1 and 5.'];
        }

        $order = $this->loadOrder($scope, $orderId, $customerId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }

        $existing = Review::query()
            ->where('food_id', $itemId)
            ->where('user_id', $customerId)
            ->where('order_id', $orderId)
            ->exists();
        if ($existing) {
            return ['success' => false, 'error' => 'You have already reviewed this item.'];
        }

        $orderItemIds = $order->details->pluck('food_id')->map(fn ($v) => (int) $v)->all();
        if (!in_array($itemId, $orderItemIds, true)) {
            return ['success' => false, 'error' => 'This item is not part of the order.'];
        }

        $item = Food::query()->find($itemId);
        if (!$item) {
            return ['success' => false, 'error' => 'Item not found.'];
        }

        // Upload BEFORE the transaction — uploads hit external storage and
        // aren't transactional; we don't want to roll back a DB write while
        // leaving uploaded files orphaned.
        $attachmentPaths = $this->uploadAttachments($imageFiles, 'item review');

        try {
            $reviewId = DB::transaction(function () use ($order, $item, $customerId, $itemId, $orderId, $rating, $comment, $attachmentPaths) {
                $review = new Review();
                $review->user_id       = $customerId;
                $review->food_id       = $itemId;
                $review->order_id      = $orderId;
                $review->restaurant_id = $order->restaurant_id;
                $review->comment       = $comment !== null && $comment !== '' ? $comment : null;
                $review->rating        = $rating;
                $review->attachment    = json_encode($attachmentPaths);
                $review->save();

                if ($order->OrderReference) {
                    $order->OrderReference->update(['is_reviewed' => 1]);
                }

                // Restaurant rating bucket — pass the EXISTING rating array into
                // RestaurantLogic which applies its own bucket convention.
                if ($item->restaurant) {
                    $item->restaurant->rating = RestaurantLogic::update_restaurant_rating(
                        $item->restaurant->getRawOriginal('rating'),
                        (int) $rating,
                    );
                    $item->restaurant->save();
                }

                // Food rating denorm — JSON bucket + weighted average + count.
                $item->rating     = ProductLogic::update_rating($item->getRawOriginal('rating'), (int) $rating);
                $item->avg_rating = ProductLogic::get_avg_rating(json_decode($item->rating, true));
                $item->save();
                $item->increment('rating_count');

                return (int) ($review->id ?? 0);
            });
        } catch (\Throwable $e) {
            Log::warning('Item review submit failed', [
                'order_id' => $orderId, 'food_id' => $itemId, 'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Could not submit the review. Please try again.'];
        }

        return ['success' => true, 'message' => 'Thanks for the review!', 'reviewId' => $reviewId];
    }

    public function submitDeliveryManReview(
        ?StorefrontScope $scope,
        int $orderId,
        int $customerId,
        int $rating,
        string $comment,
        array $imageFiles = [],
    ): array {
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'error' => 'Rating must be between 1 and 5.'];
        }
        $comment = trim($comment);
        if ($comment === '') {
            return ['success' => false, 'error' => 'Please share your opinion before submitting.'];
        }

        $order = $this->loadOrder($scope, $orderId, $customerId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found.'];
        }
        if (!$order->delivery_man_id || !$order->delivery_man) {
            return ['success' => false, 'error' => 'No delivery partner is assigned to this order.'];
        }

        $deliveryManId = (int) $order->delivery_man_id;

        $existing = DMReview::query()
            ->where('delivery_man_id', $deliveryManId)
            ->where('user_id', $customerId)
            ->where('order_id', $orderId)
            ->exists();
        if ($existing) {
            return ['success' => false, 'error' => 'You have already reviewed this delivery partner.'];
        }

        $attachmentPaths = $this->uploadAttachments($imageFiles, 'dm review');

        try {
            DB::transaction(function () use ($customerId, $deliveryManId, $orderId, $rating, $comment, $attachmentPaths) {
                $review = new DMReview();
                $review->user_id         = $customerId;
                $review->delivery_man_id = $deliveryManId;
                $review->order_id        = $orderId;
                $review->comment         = $comment;
                $review->rating          = $rating;
                $review->attachment      = json_encode($attachmentPaths);
                $review->save();
            });
        } catch (\Throwable $e) {
            Log::warning('DM review submit failed', [
                'order_id' => $orderId, 'delivery_man_id' => $deliveryManId, 'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => 'Could not submit the review. Please try again.'];
        }

        return ['success' => true, 'message' => 'Thanks for the review!'];
    }

    private function loadOrder(?StorefrontScope $scope, int $orderId, int $customerId): ?Order
    {
        return Order::query()
            ->with(['details', 'delivery_man', 'OrderReference'])
            ->where('id', $orderId)
            ->where('user_id', $customerId)
            ->where('is_guest', 0)
            ->where('order_status', 'delivered')
            ->when(
                $scope?->subTenantId !== null,
                fn ($q) => $q->where('restaurant_id', $scope->subTenantId),
            )
            ->first();
    }

    private function mapItemForReview($detail, array $reviewedItemIds, array $reviewSlugs): array
    {
        $itemId = (int) ($detail->food_id ?? 0);
        $snapshot = is_string($detail->food_details ?? null)
            ? (json_decode($detail->food_details, true) ?: [])
            : (is_array($detail->food_details ?? null) ? $detail->food_details : []);

        $name = $snapshot['name'] ?? ($detail->food->name ?? 'Item');
        $image = null;
        try {
            $image = $detail->food->image_full_url ?? ($snapshot['image_full_url'] ?? null);
        } catch (\Throwable) {
            $image = $snapshot['image_full_url'] ?? null;
        }

        $reviewed = in_array($itemId, $reviewedItemIds, true);
        return [
            'id'        => $itemId,
            'name'      => (string) $name,
            'image'     => $image ?: null,
            'qty'       => (int) ($detail->quantity ?? 0),
            'unitPrice' => (float) ($detail->price ?? 0),
            'reviewed'  => $reviewed,
            'reviewId'  => $reviewed ? ($reviewSlugs[$itemId] ?? null) : null,
        ];
    }

    private function mapDeliveryManForReview(DeliveryMan $dm, bool $reviewed): array
    {
        $ratingRow = $dm->relationLoaded('rating')
            ? $dm->getRelation('rating')->first()
            : $dm->rating()->first();
        $average = (float) ($ratingRow->average ?? 0);
        $count   = (int)   ($ratingRow->rating_count ?? 0);

        $name = trim(((string) ($dm->f_name ?? '')) . ' ' . ((string) ($dm->l_name ?? '')));
        if ($name === '') $name = 'Delivery Partner';

        return [
            'id'          => (int) $dm->id,
            'name'        => $name,
            'image'       => $this->safeImage($dm),
            'avgRating'   => round($average, 1),
            'ratingCount' => $count,
            'reviewed'    => $reviewed,
        ];
    }

    private function safeImage($model): ?string
    {
        try {
            return $model->image_full_url ?: null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function uploadAttachments(array $imageFiles, string $logContext): array
    {
        $paths = [];
        foreach ($imageFiles as $file) {
            if (!$file) continue;
            try {
                $paths[] = Helpers::upload('review/', 'png', $file);
            } catch (\Throwable $e) {
                Log::warning("{$logContext} attachment upload failed", ['error' => $e->getMessage()]);
            }
        }
        return $paths;
    }
}
