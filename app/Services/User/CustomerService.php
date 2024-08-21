<?php

namespace App\Services\User;

use App\Enum\UserType;
use App\Http\Resources\AccountOverviewResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SellerProductResource;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Trait\HttpResponse;

class CustomerService
{
    use HttpResponse;

    public function dashboardAnalytics($userId)
    {
        $currentUser = auth()->user();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('userOrders')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $total_order = $user->userOrders->count();

        $data = [
            'total_order' => $total_order,
            'total_affiliate_invite' => 0,
            'points_earned' => 0,
        ];

        return $this->success($data, "Dashboard analytics");
    }

    public function userShopByCountry($countryId)
    {
        $country = Country::where('id', $countryId)->first();

        if(!$country) {
            return $this->error(null, "Country not found", 404);
        }

        $products = Product::where('country_id', $country->id)->get();

        $data = SellerProductResource::collection($products);

        return $this->success($data, "You are now shopping in {$country->name}");
    }

    public function acountOverview($userId)
    {
        $currentUser = auth()->user();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = new AccountOverviewResource($user);

        return $this->success($data, "Account overview");
    }

    public function recentOrders($userId)
    {
        $currentUser = auth()->user();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $orders = Order::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->take(7)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Recent Orders");
    }

    public function getOrders($userId)
    {
        $currentUser = auth()->user();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $orders = Order::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->paginate(25);

        $data = OrderResource::collection($orders);

        return [
            'status' => 'true',
            'message' => 'All Orders',
            'data' => $data,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'prev_page_url' => $orders->previousPageUrl(),
                'next_page_url' => $orders->nextPageUrl(),
            ],
        ];
    }

    public function getOrderDetail($orderNo)
    {
        $order = Order::with(['product', 'user'])
        ->where('order_no', $orderNo)
        ->get();

        $data = OrderDetailResource::collection($order);

        return $this->success($data, "Order detail");
    }
}


