<?php

namespace App\Services\Admin;

use App\Enum\OrderStatus;
use App\Enum\UserStatus;
use App\Enum\UserType;
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
        $total_sales = Order::select('shop_countries.currency', 'orders.total_amount')
            ->join('shop_countries', 'orders.country_id', '=', 'shop_countries.country_id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->get()
            ->sum(fn($order) => currencyConvert($order->currency, $order->total_amount, "USD"));

        $userStats = User::selectRaw("
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN type = ? AND status != ? THEN 1 ELSE 0 END) as inactive_sellers,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as total_sellers,
                SUM(CASE WHEN is_affiliate_member = true AND status = ? THEN 1 ELSE 0 END) as active_affiliate_users,
                SUM(CASE WHEN is_affiliate_member = true AND status != ? THEN 1 ELSE 0 END) as inactive_affiliate_users
            ", [
                UserStatus::ACTIVE,
                UserType::SELLER, UserStatus::ACTIVE,
                UserType::SELLER,
                UserStatus::ACTIVE,
                UserStatus::ACTIVE
            ])
            ->first();

        $data = [
            'total_sales' => $total_sales,
            'active_users' => $userStats->active_users,
            'inactive_sellers' => $userStats->inactive_sellers,
            'total_sellers' => $userStats->total_sellers,
            'active_affiliate_users' => $userStats->active_affiliate_users,
            'inActive_affiliate_users' => $userStats->inactive_affiliate_users,
        ];

        return $this->success($data, "Dashboard Analytics");
    }

    public function bestSellers()
    {
        $bestSellers = User::select([
                DB::raw('CONCAT(users.first_name, " ", users.last_name) as seller_name'),
                'users.default_currency'
            ])
            ->leftJoin('products', 'users.id', '=', 'products.user_id')
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->whereNotNull('orders.id')
            ->selectRaw('COALESCE(SUM(orders.total_amount), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(order_items.product_quantity), 0) as sold_count')
            ->selectRaw('COALESCE(COUNT(DISTINCT orders.id), 0) as orders_count')
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.default_currency')
            ->havingRaw('total_revenue > 0 OR sold_count > 0 OR orders_count > 0')
            ->orderByDesc('total_revenue')
            ->get();

        return $this->success($bestSellers, "Best sellers based on delivered orders");
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




