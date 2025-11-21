<?php

namespace App\Services\Admin;

use App\Enum\OrderStatus;
use App\Http\Resources\AdminOrderResource;
use App\Models\Order;
use App\Trait\HttpResponse;
use Illuminate\Database\Eloquent\Builder;

class OrderService
{
    use HttpResponse;

    public function __construct(private readonly \Illuminate\Contracts\Routing\UrlGenerator $urlGenerator) {}

    public function orderAnalytics()
    {
        $all_orders = Order::count();
        $orders = Order::sum('total_amount');
        $pendingorder_amount = Order::where('status', OrderStatus::PENDING)->sum('total_amount');
        $shippedorder_amount = Order::where('status', OrderStatus::SHIPPED)->sum('total_amount');
        $deliveredorder_amount = Order::where('status', OrderStatus::DELIVERED)->sum('total_amount');

        $all_order_amount = abbreviateNumber($orders);
        $pending_order_amount = abbreviateNumber($pendingorder_amount);
        $shipped_order_amount = abbreviateNumber($shippedorder_amount);
        $delivered_order_amount = abbreviateNumber($deliveredorder_amount);

        $cancelled_order = Order::where('status', OrderStatus::CANCELLED)->count();
        $pending_order = Order::where('status', OrderStatus::PENDING)->count();
        $shipped_order = Order::where('status', OrderStatus::SHIPPED)->count();
        $delivered_order = Order::where('status', OrderStatus::DELIVERED)->count();

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

        $orders = Order::with([
            'user',
            'products.category',
            'products.user',
        ])
            ->where('country_id', 160)
            ->when($search, function ($query, $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('user', function (Builder $query) use ($search): void {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('products.user', function (Builder $query) use ($search): void {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
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

        $orders = Order::with([
            'user',
            'products.category',
            'products.user',
        ])
            ->where('country_id', '!=', 160)
            ->when($search, function ($query, $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('user', function (Builder $query) use ($search): void {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    })->orWhereHas('products.user', function (Builder $query) use ($search): void {
                        $query->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
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
        $order = Order::with(['user', 'products'])->findOrFail($id);

        $data = new AdminOrderResource($order);

        return [
            'status' => 'true',
            'message' => 'Order detail',
            'data' => $data,
        ];
    }

    public function searchOrder($request): array
    {
        $query = Order::query();

        $query->join('products', 'orders.product_id', '=', 'products.id');

        if ($request->has('name') && filled($request->input('name'))) {
            $productName = $request->input('name');
            $query->where('products.name', 'like', "%$productName%");
        }

        $orders = $query->select('orders.*')->paginate(25);

        $data = AdminOrderResource::collection($orders);

        return [
            'status' => 'true',
            'message' => 'Order search',
            'data' => $data,
        ];
    }
}
