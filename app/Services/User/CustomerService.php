<?php

namespace App\Services\User;

use App\Enum\RedeemPointStatus;
use App\Enum\UserType;
use App\Http\Resources\AccountOverviewResource;
use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SellerProductResource;
use App\Http\Resources\WishlistResource;
use App\Models\Country;
use App\Models\CustomerSupport;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Trait\HttpResponse;

class CustomerService
{
    use HttpResponse;

    public function dashboardAnalytics(int $userId)
    {
        $currentUser = userAuth();

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

        $products = Product::with(['category', 'subCategory', 'shopCountry'])
            ->where('country_id', $country->id)
            ->get();

        $data = SellerProductResource::collection($products);

        return $this->success($data, "You are now shopping in {$country->name}");
    }

    public function acountOverview(int $userId)
    {
        $currentUser = userAuth();

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

    public function recentOrders(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $orders = Order::with('user')->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->take(7)
        ->get();

        $data = OrderResource::collection($orders);

        return $this->success($data, "Recent Orders");
    }

    public function getOrders($userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $orders = Order::with('user')
            ->where('user_id', $userId)
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
        $order = Order::with(['products', 'user'])
        ->where('order_no', $orderNo)
        ->get();

        $data = OrderDetailResource::collection($order);

        return $this->success($data, "Order detail");
    }

    public function rateOrder($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('orderRate')->find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->orderRate()->create([
            'order_no' => $request->order_no,
            'rating' => $request->rating,
            'description' => $request->description
        ]);

        return $this->success(null, "Rating successful");
    }

    public function support($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        CustomerSupport::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'type' => $request->type,
            'description' => $request->description,
            'status' => 'active'
        ]);

        return $this->success(null, "Sent successfully");
    }

    public function wishlist($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with(['wishlist', 'orderRate'])->find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $product = Product::find($request->product_id);

        if (!$product) {
            return $this->error(null, "Product not found", 404);
        }

        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            return $this->error(null, "Product already in wishlist", 409);
        }

        $user->wishlist()->create([
            'product_id' => $product->id,
        ]);

        return $this->success(null, "Product added to wishlist!");
    }

    public function getWishlist(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('wishlist')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $wishlists = $user->wishlist()->with('product.category')->get();
        $data = WishlistResource::collection($wishlists);

        return $this->success($data, "Wishlists");
    }

    public function getSingleWishlist(int $userId, $id)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('wishlist')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $wishlist = $user->wishlist()->with('product.category')->find($id);

        if (!$wishlist) {
            return $this->error(null, "Wishlist not found", 404);
        }

        $data = new WishlistResource($wishlist);

        return $this->success($data, "Wishlist");
    }

    public function removeWishlist($userId, $id)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('wishlist')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $id)->first();

        if ($wishlist) {
            $wishlist->delete();
        } else {
            return $this->error(null, "Not found", 404);
        }

        return $this->success(null, "Product removed from wishlist!");
    }

    public function rewardDashboard(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('userActions')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $points = $user->userActions()->sum('points');

        $data = (object) [
            'points_earned' => (int)$points,
            'points_cleared' => 0,
        ];

        return $this->success($data, "Points");
    }

    public function activity(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('userActivityLog')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = $user->userActivityLog->map(function ($log): array {
            return [
                'id' => $log->id,
                'description' => $log->description,
                'points' => $log->points_awarded,
                'status' => $log->status,
                'date' => $log->created_at,
            ];
        });

        return $this->success($data, "User activity");
    }

    public function redeemPoint($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('reedemPoints')
            ->findOrFail($request->user_id);

        $user->reedemPoints()->create([
            'name' => $request->name,
            'status' => RedeemPointStatus::REDEEMED
        ]);

        return $this->success(null, "Points redeemed successfully");
    }
}


