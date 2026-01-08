<?php

namespace App\Services\Admin;

use App\Enum\OrderStatus;
use App\Enum\OrderType;
use App\Http\Resources\AdminOrderResource;
use App\Models\Order;
use App\Trait\HttpResponse;
use Illuminate\Database\Eloquent\Builder;

class OrderService
{
    use HttpResponse;

    public function __construct(private readonly \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator) {}

    public function orderAnalytics($request)
    {
        $isAgriEcom = $request->boolean('is_agriecom');

        $all_orders = Order::where('type', $isAgriEcom ? OrderType::AGRIECOM : OrderType::AZANY)->count();
        $orders = Order::where('type', $isAgriEcom ? OrderType::AGRIECOM : OrderType::AZANY)->sum('total_amount');

        $pendingOrderAmount = Order::where('status', OrderStatus::PENDING)
            ->filterByType($isAgriEcom)
            ->sum('total_amount');

        $shippedOrderAmount = Order::where('status', OrderStatus::SHIPPED)
            ->filterByType($isAgriEcom)
            ->sum('total_amount');
        $deliveredOrderAmount = Order::where('status', OrderStatus::DELIVERED)
            ->filterByType($isAgriEcom)
            ->sum('total_amount');

        $all_order_amount = abbreviateNumber($orders);
        $pending_order_amount = abbreviateNumber($pendingOrderAmount);
        $shipped_order_amount = abbreviateNumber($shippedOrderAmount);
        $delivered_order_amount = abbreviateNumber($deliveredOrderAmount);

        $cancelled_order = Order::where('status', OrderStatus::CANCELLED)
            ->filterByType($isAgriEcom)
            ->count();
        $pending_order = Order::where('status', OrderStatus::PENDING)
            ->filterByType($isAgriEcom)
            ->count();
        $shipped_order = Order::where('status', OrderStatus::SHIPPED)
            ->filterByType($isAgriEcom)
            ->count();
        $delivered_order = Order::where('status', OrderStatus::DELIVERED)
            ->filterByType($isAgriEcom)
            ->count();

        $data = [
            'all_orders' => [
                'count' => $all_orders,
                'total_amount' => $all_order_amount,
            ],
            'pending_orders' => [
                'count' => $pending_order,
                'total_amount' => $pending_order_amount,
            ],
            'shipped_orders' => [
                'count' => $shipped_order,
                'total_amount' => $shipped_order_amount,
            ],
            'delivered_orders' => [
                'count' => $delivered_order,
                'total_amount' => $delivered_order_amount,
            ],
            'cancelled_orders' => $cancelled_order,
        ];

        return $this->success($data, 'Analytics');
    }

    public function localOrder($request): array
    {
        $search = $request->input('search');
        $isAgriEcom = $request->boolean('is_agriecom');

        $orders = Order::withRelationShips()
            ->where('country_id', 160)
            ->filterByType($isAgriEcom)
            ->when($search, function ($query, $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('user', function (Builder $query) use ($search): void {
                        $query->whereAny(['first_name', 'last_name'], 'like', "%{$search}%");
                    })->orWhereHas('products.user', function (Builder $query) use ($search): void {
                        $query->whereAny(['first_name', 'last_name'], 'like', "%{$search}%");
                    })->orWhere('order_no', 'like', "%{$search}%");
                });
            })
            ->get()
            ->groupBy('order_no')
            ->map(function ($group) {
                $firstOrder = $group->first();
                $firstOrder->products = $group->pluck('products')->flatten();

                return $firstOrder;
            })
            ->values();

        $currentPage = $request->input('page', 1);
        $perPage = 25;
        $paginatedOrders = $orders->slice(($currentPage - 1) * $perPage, $perPage);

        $data = AdminOrderResource::collection($paginatedOrders);

        return [
            'status' => 'true',
            'message' => 'Local Orders',
            'data' => $data,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => ceil($orders->count() / $perPage),
                'per_page' => $perPage,
                'prev_page_url' => $currentPage > 1 ? $this->urlGenerator->current().'?page='.($currentPage - 1) : null,
                'next_page_url' => $currentPage < ceil($orders->count() / $perPage) ? $this->urlGenerator->current().'?page='.($currentPage + 1) : null,
            ],
        ];
    }

    public function intOrder($request): array
    {
        $search = $request->input('search');
        $isAgriEcom = $request->boolean('is_agriecom');

        $orders = Order::withRelationShips()
            ->where('country_id', '!=', 160)
            ->filterByType($isAgriEcom)
            ->when($search, function ($query, $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('user', function (Builder $query) use ($search): void {
                        $query->whereAny(['first_name', 'last_name'], 'like', "%{$search}%");
                    })->orWhereHas('products.user', function (Builder $query) use ($search): void {
                        $query->whereAny(['first_name', 'last_name'], 'like', "%{$search}%");
                    })->orWhere('order_no', 'like', "%{$search}%");
                });
            })
            ->get()
            ->groupBy('order_no')
            ->map(function ($group) {
                $firstOrder = $group->first();
                $firstOrder->products = $group->pluck('products')->flatten();

                return $firstOrder;
            })
            ->values();

        $currentPage = $request->input('page', 1);
        $perPage = 25;
        $paginatedOrders = $orders->slice(($currentPage - 1) * $perPage, $perPage);

        $data = AdminOrderResource::collection($paginatedOrders);

        return [
            'status' => 'true',
            'message' => 'International Orders',
            'data' => $data,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => ceil($orders->count() / $perPage),
                'per_page' => $perPage,
                'prev_page_url' => $currentPage > 1 ? $this->urlGenerator->current().'?page='.($currentPage - 1) : null,
                'next_page_url' => $currentPage < ceil($orders->count() / $perPage) ? $this->urlGenerator->current().'?page='.($currentPage + 1) : null,
            ],
        ];
    }

    public function orderDetail($id): array
    {
        $order = Order::withRelationShips()->findOrFail($id);
        $data = new AdminOrderResource($order);

        return [
            'status' => 'true',
            'message' => 'Order detail',
            'data' => $data,
        ];
    }

    public function searchOrder($request): array
    {
        $isAgriEcom = $request->boolean('is_agriecom');

        $orders = Order::query()
            ->filterByType($isAgriEcom)
            ->join('products', 'orders.product_id', '=', 'products.id')
            ->when(
                filled($request->input('name')),
                fn ($q) => $q->where(
                    'products.name',
                    'like',
                    '%'.$request->input('name').'%'
                )
            )
            ->select('orders.*')
            ->latest('orders.created_at')
            ->paginate(25);

        $data = AdminOrderResource::collection($orders);

        return [
            'status' => 'true',
            'message' => 'Order search',
            'data' => $data,
        ];
    }
}
