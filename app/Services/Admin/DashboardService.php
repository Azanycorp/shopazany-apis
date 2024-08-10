<?php

namespace App\Services\Admin;

use App\Enum\OrderStatus;
use App\Enum\UserStatus;

class DashboardService
{
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
            ->leftJoin('orders', 'users.id', '=', 'orders.seller_id')
            ->selectRaw('SUM(CASE WHEN orders.status = "completed" THEN orders.total_amount ELSE 0 END) as total_revenue')
            ->selectRaw('SUM(CASE WHEN orders.status = "delivered" THEN orders.product_quantity ELSE 0 END) as sold_count')
            ->selectRaw('COUNT(orders.id) as orders_count')
            ->groupBy('users.id', 'users.first_name', 'users.last_name')
            ->get();

        return $this->success($bestSellers, "Best sellers");
    }

    public function bestSellingCat()
    {
        $totalSales = Order::where('status', OrderStatus::DELIVERED)
            ->sum('total_amount');

        $bestSellingCategories = Category::select('categories.name as category_name')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->leftJoin('orders', 'products.id', '=', 'orders.product_id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->selectRaw('SUM(orders.product_quantity) as count_sold')
            ->selectRaw('SUM(orders.total_amount) as total_revenue')
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->map(function ($category) use ($totalSales) {
                $category->percentage = ($category->total_revenue / $totalSales) * 100;
                return $category;
            });

        return $this->success($bestSellingCategories, "Best selling categories");
    }

    
}




