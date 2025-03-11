<?php

namespace App\Services\Admin;

use App\Enum\OrderStatus;
use App\Enum\UserStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    use HttpResponse;

    public function dashboardAnalytics()
    {
        $total_sales = Order::where('status', OrderStatus::DELIVERED)
        ->sum('total_amount');

        $active_users = User::where('status', UserStatus::ACTIVE)
        ->count();

        $inactive_sellers = User::where('type', 'seller')
        ->where('status', UserStatus::PENDING)
        ->count();

        $sellers = User::where('type', 'seller')
        ->count();

        $data = [
            'total_sales' => $total_sales,
            'active_users' => $active_users,
            'inactive_sellers' => $inactive_sellers,
            'total_sellers' => $sellers,
        ];

        return $this->success($data, "Dashboard Analytics");
    }

    public function bestSellers()
    {
        $bestSellers = User::select(DB::raw('CONCAT(users.first_name, " ", users.last_name) as seller_name'))
            ->leftJoin('products', 'users.id', '=', 'products.user_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereNotNull('orders.id')
            ->whereIn('orders.status', [OrderStatus::SHIPPED, OrderStatus::DELIVERED])
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.status = ? THEN orders.total_amount ELSE 0 END), 0) as total_revenue', [OrderStatus::SHIPPED])
            ->selectRaw('COALESCE(SUM(CASE WHEN orders.status = ? THEN order_items.product_quantity ELSE 0 END), 0) as sold_count', [OrderStatus::DELIVERED])
            ->selectRaw('COALESCE(COUNT(DISTINCT orders.id), 0) as orders_count')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->havingRaw('total_revenue > 0 OR sold_count > 0 OR orders_count > 0')
            ->get();

        return $this->success($bestSellers, "Best sellers");
    }

    public function bestSellingCat()
    {
        $totalSales = Order::where('status', OrderStatus::DELIVERED)
            ->sum('total_amount');

        $bestSellingCategories = Category::select('categories.name as category_name')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->selectRaw('SUM(order_items.product_quantity) as count_sold')
            ->selectRaw('SUM(orders.total_amount) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->map(function ($category) use ($totalSales) {
                $category->percentage = $totalSales > 0 ? ($category->total_revenue / $totalSales) * 100 : 0;
                return $category;
            });

        return $this->success($bestSellingCategories, "Best selling categories");
    }

}




