<?php

namespace App\Services;

use App\Models\Order;
use App\Scopes\ZoneScope;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * The one query behind the customer's My Orders screen.
 *
 * All three tabs are the same list under different filters — `status=running`,
 * `status=history`, and `type=repeat` — so they are built here once rather than
 * as three endpoints that drift apart.
 *
 * A repeat order is a subscription order: StackFood keeps a single order per
 * subscription and records each occurrence in subscription_logs, so a series
 * already reads as one row and needs no collapsing.
 */
class OrderListService
{
    public const ORDER_STATUSES = Order::STATUSES;

    public const RUNNING_STATUSES = Order::ACTIVE_STATUSES;

    public function listQuery(): Builder
    {
        return Order::query()
            ->select($this->listColumns())
            ->with($this->listRelations())
            // Counted here rather than per row: `is_reviewed` is a comparison of
            // reviews against items, and `occurrence_count` is how many times a
            // subscription has come round.
            ->withCount(['details', 'subscription_logs', 'reviews'])
            ->Notpos()
            ->notHiddenForCustomer();
    }

    public function applyFilters(Builder $query, Request $request): Builder
    {
        $status = $request->input('status');
        $type = $request->input('type');

        return $query
            ->when($status === 'running', fn ($q) => $q->whereIn('order_status', self::RUNNING_STATUSES))
            ->when($status === 'history', fn ($q) => $q->whereIn('order_status', Order::TERMINAL_STATUSES))
            ->when($status && ! in_array($status, ['all', 'running', 'history'], true), fn ($q) => $q->where('order_status', $status))
            // The repeat tab asks for the subscription orders, and the other two
            // must leave them out — a series belongs to its own tab.
            ->when($type === 'repeat', fn ($q) => $q->whereNotNull('subscription_id'))
            ->when($type === 'regular' || ! $type, fn ($q) => $q->whereNull('subscription_id'))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->input('payment_status')))
            ->when($request->filled('payment_method'), fn ($q) => $q->where('payment_method', $request->input('payment_method')))
            ->when($request->filled('order_type'), fn ($q) => $q->where('order_type', $request->input('order_type')))
            ->when($request->filled('restaurant_id'), fn ($q) => $q->where('restaurant_id', (int) $request->input('restaurant_id')))
            ->when($request->filled('zone_id'), fn ($q) => $q->where('zone_id', (int) $request->input('zone_id')))
            ->when(in_array((string) $request->input('scheduled'), ['0', '1'], true), fn ($q) => $q->where('scheduled', (int) $request->input('scheduled')))
            ->when($request->filled('from') && $request->filled('to'), fn ($q) => $q->whereBetween('created_at', [
                $request->input('from').' 00:00:00',
                $request->input('to').' 23:59:59',
            ]));
    }

    /** A number is an order id; anything else is looked for in the restaurant's name. */
    public function applySearch(Builder $query, Request $request): Builder
    {
        $search = trim((string) $request->input('search'));

        if ($search === '') {
            return $query;
        }

        if (ctype_digit($search)) {
            return $query->where('orders.id', (int) $search);
        }

        return $query->whereHas('restaurant', fn ($q) => $q->where('name', 'like', "%{$search}%"));
    }

    public function paginate(Builder $query, Request $request): LengthAwarePaginator
    {
        $limit = max(1, (int) $request->input('limit', 10));
        $offset = max(1, (int) $request->input('offset', 1));

        return $query->latest('orders.id')->paginate($limit, ['*'], 'page', $offset);
    }

    public function respond(LengthAwarePaginator $paginator, string $resourceClass, string $key = 'orders'): array
    {
        return [
            'total_size' => $paginator->total(),
            'limit' => (int) $paginator->perPage(),
            'offset' => (int) $paginator->currentPage(),
            $key => $resourceClass::collection($paginator->getCollection()),
        ];
    }

    private function listColumns(): array
    {
        return [
            'id', 'user_id', 'is_guest', 'restaurant_id', 'zone_id', 'subscription_id',
            'order_amount', 'order_status', 'payment_status', 'payment_method', 'order_type',
            'coupon_code', 'scheduled', 'schedule_at', 'delivery_address',
            'created_at', 'updated_at',
        ];
    }

    /**
     * The live food or campaign is loaded for its name and image, and the
     * order's own snapshot rides along for anything since deleted. The zone
     * scope is dropped — a customer who has moved zones must still see what
     * they ordered — and `storage` because the image url accessor reads it,
     * which would otherwise be one query per row.
     *
     * `bogo_offer_id` rides along for `can_reorder`, so the flag costs no query
     * of its own.
     */
    private function listRelations(): array
    {
        return [
            'restaurant:id,name,phone,logo,zone_id',
            'restaurant.storage',
            'customer:id,f_name,l_name,phone,email,image',
            'customer.storage',
            'details:id,order_id,food_id,item_campaign_id,bogo_offer_id,food_details',
            'details.food' => fn ($query) => $query->withoutGlobalScope(ZoneScope::class)->select('id', 'name', 'image'),
            'details.food.storage',
            'details.campaign' => fn ($query) => $query->withoutGlobalScope(ZoneScope::class)->select('id', 'title', 'image'),
            'details.campaign.storage',
        ];
    }
}
