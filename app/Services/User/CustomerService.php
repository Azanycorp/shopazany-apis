<?php

namespace App\Services\User;

use App\Enum\RedeemPointStatus;
use App\Enum\UserType;
use App\Http\Resources\AccountOverviewResource;
use App\Http\Resources\CustomerOrderDetailResource;
use App\Http\Resources\CustomerOrderResource;
use App\Http\Resources\SellerProductResource;
use App\Http\Resources\WishlistResource;
use App\Models\Country;
use App\Models\CustomerSupport;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserShippingAddress;
use App\Models\Wishlist;
use App\Services\Auth\Auth;
use App\Trait\General;
use App\Trait\HttpResponse;
use Spatie\ResponseCache\Facades\ResponseCache;

class CustomerService
{
    use General, HttpResponse;

    public function __construct(
        protected Auth $auth, private readonly \Illuminate\Contracts\Config\Repository $repository,
    ) {}

    public function dashboardAnalytics(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with(['userOrders', 'wallet'])->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $total_order = $user->userOrders->count();

        $data = [
            'total_order' => $total_order,
            'total_affiliate_invite' => 0,
            'points_earned' => $user->wallet->reward_point ?? 0,
        ];

        return $this->success($data, 'Dashboard analytics');
    }

    public function userShopByCountry($countryId)
    {
        $country = Country::where('id', $countryId)->first();

        if (! $country) {
            return $this->error(null, 'Country not found', 404);
        }

        $products = Product::with([
            'category',
            'subCategory',
            'shopCountry',
            'brand',
            'color',
            'unit',
            'size',
            'orders',
            'productReviews',
        ])
            ->where('country_id', $country->id)
            ->get();

        $data = SellerProductResource::collection($products);

        return $this->success($data, "You are now shopping in {$country->name}");
    }

    public function acountOverview(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $data = new AccountOverviewResource($user);

        return $this->success($data, 'Account overview');
    }

    public function recentOrders(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $orders = Order::with([
            'user',
            'products.shopCountry',
            'products.productVariations.product',
        ])
            ->where('user_id', $userId)
            ->latest()
            ->take(7)
            ->get();

        $data = CustomerOrderResource::collection($orders);

        return $this->success($data, 'Recent Orders');
    }

    public function getOrders($userId, $request)
    {
        $currentUser = userAuth();
        $status = $request->query('status');

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $orders = Order::with([
            'user',
            'products.shopCountry',
            'products.productVariations.product',
        ])
            ->where('user_id', $userId)
            ->when($status, fn ($query) => $query->where('status', $status))->latest()
            ->paginate(25);

        $data = CustomerOrderResource::collection($orders);

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

    public function getOrderDetail($orderNo, $summary)
    {
        $order = Order::with([
            'user.userShippingAddress',
            'products.shopCountry',
            'products.productVariations.product',
            'orderActivities',
        ])
            ->where('order_no', $orderNo)
            ->first();

        if (! $order) {
            return $this->error('Order not found', 404);
        }

        $userCurrency = $order->user->default_currency ?? 'USD';
        $getSummary = $summary->handle($order, $userCurrency);

        $data = new CustomerOrderDetailResource($order);
        $data->additional(['summary' => $getSummary]);

        return $this->success($data, 'Order detail');
    }

    public function rateOrder($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('orderRate')->find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $user->orderRate()->create([
            'order_no' => $request->order_no,
            'rating' => $request->rating,
            'description' => $request->description,
        ]);

        return $this->success(null, 'Rating successful');
    }

    public function support($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        CustomerSupport::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'type' => $request->type,
            'description' => $request->description,
            'status' => 'active',
        ]);

        return $this->success(null, 'Sent successfully');
    }

    public function wishlist($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with(['wishlist', 'orderRate'])->find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $product = Product::find($request->product_id);

        if (! $product) {
            return $this->error(null, 'Product not found', 404);
        }

        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            return $this->error(null, 'Product already in wishlist', 409);
        }

        $user->wishlist()->create([
            'product_id' => $product->id,
        ]);

        ResponseCache::clear();

        return $this->success(null, 'Product added to wishlist!');
    }

    public function getWishlist(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('wishlist')->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $wishlists = $user->wishlist()->with(['product.category', 'product.shopCountry'])->get();
        $data = WishlistResource::collection($wishlists);

        return $this->success($data, 'Wishlists');
    }

    public function getSingleWishlist(int $userId, $id)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('wishlist')->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $wishlist = $user->wishlist()->with('product.category')->find($id);

        if (! $wishlist) {
            return $this->error(null, 'Wishlist not found', 404);
        }

        $data = new WishlistResource($wishlist);

        return $this->success($data, 'Wishlist');
    }

    public function removeWishlist($userId, $id)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('wishlist')->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $wishlist = Wishlist::where('user_id', $userId)->where('product_id', $id)->first();

        if ($wishlist) {
            $wishlist->delete();
        } else {
            return $this->error(null, 'Not found', 404);
        }

        return $this->success(null, 'Product removed from wishlist!');
    }

    public function rewardDashboard(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with(['userActions', 'wallet'])->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $data = (object) [
            'points_earned' => $user->wallet->reward_point ?? 0,
            'points_cleared' => $user->wallet->points_cleared ?? 0,
        ];

        return $this->success($data, 'Points');
    }

    public function activity(int $userId)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $userId || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('userActivityLog')->find($userId);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $data = $user->userActivityLog->map(function ($log): array {
            return [
                'id' => $log->id,
                'description' => $log->description,
                'points' => $log->points_awarded,
                'status' => $log->status,
                'date' => $log->created_at,
            ];
        })->toArray();

        $rewardOrders = $this->getCustomers();
        if (is_object($rewardOrders) && method_exists($rewardOrders, 'toArray')) {
            $rewardOrders = $rewardOrders->toArray();
        }

        $data = [
            'activities' => $data,
            'orders' => $rewardOrders,
        ];

        return $this->success($data, 'User activity');
    }

    public function redeemPoint($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id || $currentUser->type != UserType::CUSTOMER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('reedemPoints')
            ->findOrFail($request->user_id);

        $user->reedemPoints()->create([
            'name' => $request->name,
            'point' => $request->point,
            'status' => RedeemPointStatus::REDEEMED,
        ]);

        return $this->success(null, 'Points redeemed successfully');
    }

    public function getCategories()
    {
        $url = $this->repository->get('services.reward_service.url').'/service/all-category';
        $response = $this->auth->request('get', $url, []);

        return $response->json();
    }

    public function getServicesByCategory($slug)
    {
        $url = $this->repository->get('services.reward_service.url')."/service/category/{$slug}";
        $response = $this->auth->request('get', $url, []);

        $services = $response->json();

        if (! isset($services['data'])) {
            return $services;
        }

        $services['data'] = (new \Illuminate\Support\Collection($services['data']))->map(function (array $item) {
            $price = (float) $item['price'];
            $currency = $item['currency'];

            try {
                $item['point'] = amountToPoint($price, $currency);
            } catch (\Throwable $e) {
                $item['point'] = null;
            }

            return $item;
        })->all();

        return $services;
    }

    public function getServices($request)
    {
        $url = $this->repository->get('services.reward_service.url').'/service';
        $params = $request->only(['search']);

        $response = $this->auth->request('get', $url, $params);

        $services = $response->json();

        if (! isset($services['data'])) {
            return $services;
        }

        $services['data'] = (new \Illuminate\Support\Collection($services['data']))->map(function (array $item) {
            $price = (float) $item['price'];
            $currency = $item['currency'];

            try {
                $item['point'] = amountToPoint($price, $currency);
            } catch (\Throwable $e) {
                $item['point'] = null;
            }

            return $item;
        })->all();

        return $services;
    }

    public function getCompanies()
    {
        $url = $this->repository->get('services.reward_service.url').'/service/company';
        $response = $this->auth->request('get', $url, []);

        return $response->json();
    }

    public function getCompanyDetail($slug)
    {
        $url = $this->repository->get('services.reward_service.url')."/service/company/detail/{$slug}";
        $response = $this->auth->request('get', $url, []);

        $services = $response->json();

        if (! isset($services['data'])) {
            return $services;
        }

        $services['data']['additional_products'] = (new \Illuminate\Support\Collection($services['data']['additional_products']))->map(function (array $item) {
            $price = (float) $item['price'];
            $currency = $item['currency'];

            try {
                $item['point'] = amountToPoint($price, $currency);
            } catch (\Throwable $e) {
                $item['point'] = null;
            }

            return $item;
        })->all();

        return $services;
    }

    public function purchaseService($request)
    {
        $user = User::with('wallet')->find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        if ((int) $request->point <= 0) {
            return $this->error(null, 'Reward point must be greater than zero', 422);
        }

        if (! $user->wallet) {
            return $this->error(null, 'User wallet not found', 404);
        }

        if ($user->wallet->reward_point < $request->point) {
            return $this->error(null, 'Insufficient reward point', 400);
        }

        $price = pointConvert($request->point, $user->default_currency);

        $url = $this->repository->get('services.reward_service.url').'/service/purchase';

        $params = [
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone ?? '00000000000',
            'address' => $user->address ?? 'No Address',
            'city' => $user->city,
            'state' => $user->state_id,
            'product_id' => $request->product_id,
            'point' => $request->point,
            'price' => $price,
            'country_id' => $user->country,
        ];

        try {
            $response = $this->auth->request('post', $url, $params);
            $status = $response->status();
            $data = $response->json();

            if ($result = $this->handleRewardValidation($status, $data)) {
                return $result;
            }

            if (isset($data['status']) && $data['status'] === true) {
                $user->wallet()->decrement('reward_point', $request->point);
                $user->wallet()->increment('points_cleared', $request->point);
            }

            return $this->success(null, $data['message'] ?? 'Service purchased successfully.');
        } catch (\Exception $e) {
            return $this->error(null, "Something went wrong. Please try again later. {$e->getMessage()}", 500);
        }
    }

    public function getCustomers()
    {
        $user = userAuth();
        $url = $this->repository->get('services.reward_service.url').'/service/customer/orders';

        try {
            $response = $this->auth->request(
                'get',
                $url,
                ['email' => $user->email]
            );

            $services = $response->json();

            if (! isset($services['data'])) {
                return $services;
            }

            return $services['data']['orders'] ?? [];
        } catch (\Exception $e) {
            return $this->error(null, "Something went wrong. Please try again later. {$e->getMessage()}", 500);
        }
    }

    public function shipping($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found!', 404);
        }

        $addr = UserShippingAddress::updateOrCreate(
            [
                'user_id' => $user->id,
                'street_address' => $request->street_address,
            ],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'state' => $request->state,
                'city' => $request->city,
                'zip' => $request->zip,
            ]
        );

        $msg = $addr->wasRecentlyCreated ? 'Shipping address created successfully.' : 'Shipping address updated successfully.';
        $code = $addr->wasRecentlyCreated ? 201 : 200;

        return $this->success(null, $msg, $code);
    }
}
