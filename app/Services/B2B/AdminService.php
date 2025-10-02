<?php

namespace App\Services\B2B;

use App\Enum\AdminStatus;
use App\Enum\AdminType;
use App\Enum\BannerType;
use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Enum\PlanStatus;
use App\Enum\PlanType;
use App\Enum\ProductStatus;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\B2BSellerResource;
use App\Http\Resources\BlogResource;
use App\Http\Resources\ClientLogoResource;
use App\Http\Resources\ShippingAgentResource;
use App\Http\Resources\SocialLinkResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Mail\B2BNewAdminEmail;
use App\Models\Admin;
use App\Models\B2bCompany;
use App\Models\B2bOrder;
use App\Models\B2BProduct;
use App\Models\Blog;
use App\Models\ClientLogo;
use App\Models\Configuration;
use App\Models\PageBanner;
use App\Models\Rfq;
use App\Models\ShippingAgent;
use App\Models\SocialSetting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Repositories\B2BProductRepository;
use App\Repositories\B2BSellerShippingRepository;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminService
{
    use HttpResponse, SignUp;

    public function __construct(
        protected B2BProductRepository $b2bProductRepository,
        protected B2BSellerShippingRepository $b2bSellerShippingRepository
    ) {}

    // dashboard
    public function dashboard()
    {
        $users = User::all();
        $orders = B2bOrder::orderStats();
        $rfqs = Rfq::with(['buyer', 'seller'])->latest()->get();
        $completion_request = B2bOrder::where('status', OrderStatus::SHIPPED)->take(3)->get();

        $data = [
            'buyers' => $users->where('type', UserType::B2B_BUYER)->count(),
            'sellers' => $users->where('type', UserType::B2B_SELLER)->count(),
            'ongoing_deals' => $orders->total_pending,
            'all_time_orders_count' => $orders->total_orders,
            'all_time_orders_amount' => $orders->total_order_delivered_amount,
            'ongoing' => $orders->total_pending,
            'last_seven_days' => $orders->total_order_count_week,
            'last_thirty_days' => $orders->total_order_amount_month,
            'recent_rfqs' => $rfqs,
            'completion_request' => $completion_request,
        ];

        return $this->success($data, 'Dashboard details');
    }

    public function getAllRfq()
    {
        $rfqs = Rfq::with(['buyer', 'seller'])->latest()->get();
        $active_rfqs = Rfq::where('status', OrderStatus::COMPLETED)->count();
        $users = User::all();

        $data = [
            'buyers' => $users->where('type', UserType::B2B_BUYER)->count(),
            'sellers' => $users->where('type', UserType::B2B_SELLER)->count(),
            'active_rfqs' => $active_rfqs,
            'recent_rfqs' => $rfqs,
        ];

        return $this->success($data, 'rfqs');
    }

    public function getRfqDetails($id)
    {
        $order = Rfq::with(['buyer', 'seller'])->findOrFail($id);

        return $this->success($order, 'Rfq details');
    }

    public function getAllOrders()
    {
        $searchQuery = request()->input('search');
        $orders = B2bOrder::orderStats();
        $international_orders = B2bOrder::when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
            $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                $subQuery->where('country_id', '!=', 160)
                    ->where('order_no', 'LIKE', '%'.$searchQuery.'%');
            });
        })->get();

        $local_orders = B2bOrder::with(['buyer', 'seller'])->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
            $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                $subQuery->where('country_id', 160)
                    ->where('order_no', 'LIKE', '%'.$searchQuery.'%');
            });
        })->get();

        $data = [
            'all_orders' => $orders->total_orders,
            'cancelled_orders' => $orders->total_cancelled,
            'pending_orders' => $orders->total_pending,
            'shipped_orders' => $orders->total_shipped,
            'delivered_orders' => $orders->total_delivered,
            'local_orders' => $local_orders,
            'international_orders' => $international_orders,

        ];

        return $this->success($data, 'orders');
    }

    public function getOrderDetails($id)
    {
        $order = B2bOrder::with(['buyer', 'seller'])->findOrFail($id);

        return $this->success($order, 'Order details');
    }

    public function markCompleted($id)
    {
        $order = B2bOrder::findOrFail($id);

        $order->update([
            'status' => OrderStatus::DELIVERED,
        ]);

        return $this->success(null, 'Order Completed');
    }

    public function cancelOrder($id)
    {
        DB::beginTransaction();

        try {
            $order = B2bOrder::findOrFail($id);
            $order->update([
                'status' => OrderStatus::CANCELLED,
            ]);

            $product = B2BProduct::find($order->product_id);
            if (! $product) {
                return $this->error(null, 'Product not found', 404);
            }

            $product->availability_quantity += $order->product_quantity;
            $product->sold -= $order->product_quantity;
            $product->save();

            DB::commit();

            return $this->success(null, 'Order cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(null, 'Failed to cancel order: '.$e->getMessage(), 500);
        }
    }

    // Sellers
    // Admin section
    public function allSellers()
    {
        $type = request()->query('type');

        $sellers = User::withCount('b2bProducts')
            ->when($type, fn($q) => $q->where('type', $type))
            ->where('type', UserType::B2B_SELLER)
            ->latest()
            ->get();

        $sellersCounts = User::where('type', $type ?? UserType::B2B_SELLER)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END) as inactive
            ', [
                UserStatus::ACTIVE,
                UserStatus::PENDING,
                UserStatus::BLOCKED,
                UserStatus::SUSPENDED,
            ])
            ->first();

        $data = [
            'sellers_count' => $sellersCounts->total,
            'active' => $sellersCounts->active,
            'inactive' => $sellersCounts->inactive,
            'sellers' => $sellers,
        ];

        return $this->success($data, 'sellers details');
    }

    public function approveSeller($id)
    {
        $user = User::where('id', $id)
            ->firstOrFail();

        $user->is_admin_approve = ! $user->is_admin_approve;
        $user->status = $user->is_admin_approve ? UserStatus::ACTIVE : UserStatus::BLOCKED;
        $user->save();

        $status = $user->is_admin_approve ? 'Approved successfully' : 'Disapproved successfully';

        return $this->success(null, $status);
    }

    public function viewSeller($id): array
    {
        $user = User::where('id', $id)
            ->firstOrFail();

        $search = request()->search;
        $data = new B2BSellerResource($user);
        $query = B2BProduct::with(['b2bProductImages', 'category', 'country', 'user', 'subCategory'])
            ->where('user_id', $id);

        if (! empty($search)) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orWhereHas('category', function ($q) use ($search): void {
                    $q->where('name', 'like', '%'.$search.'%');
                });
        }

        return [
            'status' => 'true',
            'message' => 'Seller details',
            'data' => $data,
            'products' => $query->latest('id')->get(),
        ];
    }

    public function editSeller($request, $id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email_address,
            'phone' => $request->phone_number,
            'password' => bcrypt($request->passowrd),
        ]);

        return $this->success(['user_id' => $user->id], 'Updated successfully');
    }

    public function banSeller($id)
    {
        $user = User::where('id', $id)
            ->firstOrFail();

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;
        $user->save();

        return $this->success(null, 'User has been blocked successfully');
    }

    public function removeSeller($id)
    {
        $user = User::where('id', $id)
            ->firstOrFail();

        $user->delete();

        return $this->success(null, 'User removed successfully');
    }

    public function bulkRemove($request)
    {
        $type = request()->query('type');
        $users = User::where('type', $type ?? UserType::B2B_SELLER)
            ->whereIn('id', $request->user_ids)
            ->get();

        if ($users->isEmpty()) {
            return $this->error(null, 'No matching users found.', 404);
        }

        User::whereIn('id', $users->pluck('id'))->update([
            'status' => UserStatus::DELETED,
            'is_verified' => 0,
            'is_admin_approve' => 0,
        ]);

        User::whereIn('id', $users->pluck('id'))->delete();

        return $this->success(null, 'User(s) have been removed successfully');
    }

    // Seller Product
    public function addSellerProduct($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $parts = explode('@', $user->email);
        $name = $parts[0];
        $res = folderNames('b2bproduct', $name, 'front_image');
        $slug = Str::slug($request->name);

        if (B2BProduct::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        if ($request->hasFile('front_image')) {
            $url = uploadImage($request, 'front_image', $res->frontImage);
        }

        $data = [
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'keywords' => $request->keywords,
            'description' => $request->description,
            'front_image' => $url['url'] ?? null,
            'public_id' => $url['public_id'] ?? null,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'unit_price' => $request->unit,
            'quantity' => $request->quantity,
            'availability_quantity' => $request->quantity,
            'fob_price' => $request->fob_price,
            'country_id' => is_int($user->country) ? $user->country : 160,
        ];

        $product = $this->b2bProductRepository->create($data);

        if ($request->hasFile('images')) {
            $folder = folderNames('product', $name, null, 'images');
            uploadMultipleB2BProductImage($request, 'images', $folder->folder, $product);
        }

        return $this->success(null, 'Product added successfully', 201);
    }

    public function viewSellerProduct($user_id, $product_id)
    {
        $user = User::find($user_id);

        $prod = B2BProduct::where('user_id', $user->id)->find($product_id);

        if (! $user) {
            return $this->error(null, 'No user found.', 404);
        }

        if (! $prod) {
            return $this->error(null, 'No product found.', 404);
        }

        return $this->success(new B2BProductResource($prod), 'Product details');
    }

    public function editSellerProduct($request, $user_id, int $product_id)
    {
        $prod = B2BProduct::find($product_id);

        if (! $prod) {
            return $this->error(null, 'No Product found.', 404);
        }

        $user = User::find($user_id);

        if (! $user) {
            return $this->error(null, 'No user found.', 404);
        }

        if ($prod->user_id != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $parts = explode('@', $user->email);
        $name = $parts[0];

        $res = folderNames('b2bproduct', $name, 'front_image');

        if ($request->name) {
            $slug = Str::slug($request->name);

            if (B2BProduct::where('slug', $slug)->exists()) {
                $slug = $slug.'-'.uniqid();
            }
        } else {
            $slug = $prod->slug;
        }

        if ($request->hasFile('front_image')) {
            $url = uploadImage($request, 'front_image', $res->frontImage);
        }

        $data = [
            'user_id' => $user_id,
            'name' => $request->name ?? $prod->name,
            'slug' => $slug,
            'category_id' => $request->category_id ?? $prod->category_id,
            'sub_category_id' => $request->sub_category_id ?? $prod->name,
            'keywords' => $request->keywords ?? $prod->keywords,
            'description' => $request->description ?? $prod->description,
            'front_image' => $url['url'] ?? $prod->front_image,
            'public_id' => $url['public_id'] ?? $prod->public_id,
            'quantity' => $request->quantity ?? $prod->quantity,
            'minimum_order_quantity' => $request->minimum_order_quantity ?? $prod->minimum_order_quantity,
            'unit_price' => $request->unit ?? $prod->unit_price,
            'country_id' => $user->country ?? 160,
        ];

        $product = $this->b2bProductRepository->update($product_id, $data);

        if ($request->hasFile('images')) {
            $product->b2bProductImages()->delete();
            $folder = folderNames('product', $name, null, 'images');
            uploadMultipleB2BProductImage($request, 'images', $folder->folder, $product);
        }

        return $this->success(null, 'Product updated successfully');
    }

    public function removeSellerProduct($user_id, $product_id)
    {
        $user = User::find($user_id);

        if (! $user) {
            return $this->error(null, 'No user found.', 404);
        }

        $prod = B2BProduct::where('user_id', $user->id)->find($product_id);

        if (! $prod) {
            return $this->error(null, 'No product found.', 404);
        }

        $prod->delete();

        return $this->success(null, 'Product Deleted successfully');
    }

    public function allBuyers()
    {
        $type = request()->query('type');

        $buyerStats = User::when($type, fn($q) => $q->where('type', $type))->where('type', UserType::B2B_BUYER)
            ->selectRaw('
                COUNT(*) as total_buyers,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_buyers,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_buyers
            ', [
                UserStatus::ACTIVE,
                UserStatus::PENDING,
            ])
            ->first();

        $buyers = User::with('b2bCompany')
            ->when($type, fn($q) => $q->where('type', $type))
            ->latest()
            ->get();

        $data = [
            'buyers_count' => $buyerStats->total_buyers,
            'active_buyers' => $buyerStats->active_buyers,
            'pending_buyers' => $buyerStats->pending_buyers,
            'buyers' => $buyers,
        ];

        return $this->success($data, 'Buyers retrieved successfully.');
    }

    public function viewBuyer($id)
    {
        $user = User::select('id', 'first_name', 'last_name', 'email', 'image')
            ->with('b2bCompany')
            ->where('id', $id)
            ->firstOrFail();

        return $this->success($user, 'Buyer details');
    }

    public function editBuyer($request, $id)
    {
        $user = User::findOrFail($id);

        if (! empty($request->email) && User::where('email', $request->email)->where('id', '!=', $id)->exists()) {
            return $this->error(null, 'Email already exists.');
        }

        $image = $request->hasFile('image') ?
            uploadUserImage($request, 'image', $user) :
            ['url' => $user->image, 'public_id' => $user->public_id];

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'email' => $request->email ?? $user->email,
            'image' => $image['url'],
            'public_id' => $image['public_id'],
        ]);

        return $this->success($user, 'Buyer details');
    }

    public function editBuyerCompany($request, $id)
    {
        $user = User::findOrFail($id);

        $company = B2bCompany::where('user_id', $user->id)->first();

        if (! $company) {
            return $this->error(null, 'No company found to update', 404);
        }

        $company->update([
            'business_name' => $request->business_name ?? $company->business_name,
            'company_size' => $request->company_size ?? $company->company_size,
            'website' => $request->website ?? $company->website,
            'service_type' => $request->service_type ?? $company->service_type,
        ]);

        return $this->success($company, 'company details');
    }

    public function removeBuyer($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->success(null, 'User removed successfully');
    }

    public function bulkRemoveBuyer($request)
    {
        $users = User::whereIn('id', $request->user_ids)->get();

        if ($users->isEmpty()) {
            return $this->error(null, 'No matching users found.', 404);
        }

        User::whereIn('id', $users->pluck('id'))->update([
            'status' => UserStatus::DELETED,
            'is_verified' => 0,
            'is_admin_approve' => 0,
        ]);

        User::whereIn('id', $users->pluck('id'))->delete();

        return $this->success(null, 'User(s) have been removed successfully');
    }

    public function approveBuyer($id)
    {
        $user = User::findOrFail($id);
        $user->is_admin_approve = ! $user->is_admin_approve;
        $user->status = $user->is_admin_approve ? UserStatus::ACTIVE : UserStatus::BLOCKED;

        $user->save();

        $status = $user->is_admin_approve ? 'Approved successfully' : 'Disapproved successfully';

        return $this->success(null, $status);
    }

    public function banBuyer($id)
    {
        $user = User::findOrFail($id);
        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, 'User has been blocked successfully');
    }

    // CMS / Promo and banners
    public function adminProfile()
    {
        $authUser = userAuth();

        $user = Admin::where('type', AdminType::B2B)
            ->where('id', $authUser->id)
            ->firstOrFail();

        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function updateAdminProfile($request)
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminType::B2B)
            ->where('id', $authUser->id)
            ->firstOrFail();

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);
        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function enableTwoFactor($request)
    {
        $authUser = userAuth();

        $user = Admin::where('type', AdminType::B2B)
            ->where('id', $authUser->id)
            ->firstOrFail();

        $user->update([
            'two_factor_enabled' => $request->two_factor_enabled,
        ]);

        return $this->success('Settings updated');
    }

    public function updateAdminPassword($request)
    {
        $authUser = userAuth();

        $user = Admin::where('type', AdminType::B2B)
            ->where('id', $authUser->id)
            ->firstOrFail();

        if (! Hash::check($request->old_password, $user->password)) {
            return $this->error('Old password is incorrect.', 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return $this->success(null, 'Password updated');
    }

    public function getConfigDetails()
    {
        $config = Configuration::firstOrFail();

        return $this->success($config, 'Config details');
    }

    public function updateConfigDetails($request)
    {
        $configData = $request->only([
            'usd_rate',
            'company_profit',
            'email_verify',
            'currency_code',
            'currency_symbol',
            'promotion_start_date',
            'promotion_end_date',
            'min_deposit',
            'max_deposit',
            'min_withdrawal',
            'withdrawal_frequency',
            'withdrawal_status',
            'max_withdrawal',
            'withdrawal_fee',
            'seller_perc',
            'paystack_perc',
            'paystack_fixed',
        ]);

        Configuration::updateOrCreate([], $configData);

        return $this->success(null, 'Details updated');
    }

    public function getPageBanners()
    {
        $banners = PageBanner::select('id', 'page', 'section', 'type', 'banner_url')->where('type', BannerType::B2B)->latest()->get();

        return $this->success($banners, 'Banners');
    }

    public function updatePageBanner($request, $id)
    {
        $banner = PageBanner::where('type', BannerType::B2B)
            ->where('id', $id)->firstOrFail();

        if ($banner && $request->hasFile('banner_url')) {
            $banner_url = uploadImage($request, 'banner_url', 'home-banner');
        }

        $banner->update([
            'page' => $request->page ?? $banner->page,
            'section' => $request->section ?? $banner->section,
            'type' => BannerType::B2B,
            'banner_url' => $banner_url['url'] ?? $banner->banner_url,
        ]);

        return $this->success(null, 'Details updated');
    }

    public function addPageBanner($request)
    {
        $banner_url = $request->hasFile('banner_url') ?
            uploadImage($request, 'banner_url', 'home-banner') :
            ['url' => null];

        PageBanner::create([
            'page' => $request->page,
            'section' => $request->section,
            'type' => BannerType::B2B,
            'banner_url' => $banner_url['url'],
        ]);

        return $this->success(null, 'Banner added', 201);
    }

    public function getPageBanner($id)
    {
        $banner = PageBanner::select('id', 'page', 'section', 'type', 'banner_url')
            ->where('type', BannerType::B2B)
            ->where('id', $id)->firstOrFail();

        return $this->success($banner, 'Banner details');
    }

    public function deletePageBanner($id)
    {
        $banner = PageBanner::where('type', BannerType::B2B)->where('id', $id)->firstOrFail();

        $banner->delete();

        return $this->success(null, 'Details Deleted');
    }

    // seller withdrawal request
    public function widthrawalRequests()
    {
        $payouts = WithdrawalRequest::select(['id', 'user_id', 'amount', 'status', 'created_at'])
            ->with(['user' => function ($query): void {
                $query->select('id', 'first_name', 'last_name')->where('type', UserType::B2B_SELLER);
            }])->latest()->get();

        if ($payouts->isEmpty()) {
            return $this->error(null, 'No record found');
        }

        return $this->success($payouts, 'Withdrawal requests');
    }

    // seller Product request
    public function allProducts()
    {
        $products = B2BProduct::with(['user' => function ($query): void {
            $query->select('id', 'first_name', 'last_name')
                ->where('type', UserType::B2B_SELLER);
        }])
            ->whereStatus(OrderStatus::PENDING)
            ->latest()
            ->get();

        return $this->success($products, 'Products listing');
    }

    public function viewProduct($id)
    {
        $product = B2BProduct::with(['user' => function ($query): void {
            $query->select('id', 'first_name', 'last_name')
                ->where('type', UserType::B2B_SELLER);
        }])
            ->where('id', $id)
            ->firstOrFail();

        return $this->success($product, 'Product details');
    }

    public function approveProduct($id)
    {
        $product = B2BProduct::findOrFail($id);

        $product->update([
            'status' => ProductStatus::ACTIVE,
        ]);

        return $this->success(null, 'Product Approved');
    }

    public function rejectProduct($request, $id)
    {
        $product = B2BProduct::findOrFail($id);

        $product->update([
            'status' => ProductStatus::DECLINED,
            'admin_comment' => $request->note,
        ]);

        return $this->success(null, 'Comment Submitted successfully');
    }

    // Subscription Plans
    public function b2bSubscriptionPlans()
    {
        $plans = SubscriptionPlan::where('type', PlanType::B2B)->latest()->get();

        return $this->success(SubscriptionPlanResource::collection($plans), 'All B2B Plans');
    }

    public function addSubscriptionPlan($request)
    {
        $currencyCode = $this->currencyCode($request);

        $plan = SubscriptionPlan::create([
            'title' => $request->title,
            'cost' => $request->cost,
            'country_id' => $request->country_id,
            'currency' => $currencyCode,
            'period' => $request->period,
            'tier' => $request->tier,
            'designation' => $request->designation,
            'tagline' => $request->tagline,
            'details' => $request->details,
            'type' => PlanType::B2B,
            'status' => PlanStatus::ACTIVE,
        ]);

        return $this->success(new SubscriptionPlanResource($plan), 'Plan added successfully', 201);
    }

    public function viewSubscriptionPlan($id)
    {
        $plan = SubscriptionPlan::where('type', PlanType::B2B)->find($id);
        if (! $plan) {
            return $this->error(null, 'Plan not found', 404);
        }

        $data = new SubscriptionPlanResource($plan);

        return $this->success($data, 'Plan details');
    }

    public function editSubscriptionPlan($request, $id)
    {
        $plan = SubscriptionPlan::where('type', PlanType::B2B)->find($id);

        if (! $plan) {
            return $this->error(null, 'Plan not found', 404);
        }

        $currencyCode = $this->currencyCode($request);

        $plan->update([
            'title' => $request->title,
            'cost' => $request->cost,
            'country_id' => $request->country_id,
            'currency' => $request->country_id ? $currencyCode : $plan->currency,
            'period' => $request->period,
            'tier' => $request->tier,
            'designation' => $request->designation,
            'tagline' => $request->tagline,
            'details' => $request->details,
            'status' => $request->status ?? PlanStatus::ACTIVE,
        ]);

        return $this->success(null, 'Details updated successfully');
    }

    public function deleteSubscriptionPlan($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        if ($plan->type !== PlanType::B2B) {
            return $this->error(null, 'Invalid plan type', 400);
        }

        if ($plan->subscriptions()->exists()) {
            return $this->error(null, 'Plan cannot be deleted because it has active subscriptions', 400);
        }

        $plan->delete();

        return $this->success(null, 'Plan deleted successfully.');
    }

    // Blog Section
    public function allBlogs()
    {
        $currentUserId = userAuthId();

        $blogs = Blog::with('user')
            ->where('admin_id', $currentUserId)
            ->where('type', BannerType::B2B)
            ->latest()
            ->get();

        return $this->success(BlogResource::collection($blogs), 'Added Blogs');
    }

    public function addBlog($request)
    {
        $currentUserId = userAuthId();
        $url = uploadImage($request, 'image', 'blog');

        $plan = Blog::create([
            'admin_id' => $currentUserId,
            'title' => $request->title,
            'type' => BannerType::B2B,
            'slug' => Str::slug($request->title),
            'description' => $request->description,
            'image' => $url['url'],
            'public_id' => $url['public_id'],
        ]);

        return $this->success($plan, 'Plan added successfully', 201);
    }

    public function getBlog($id)
    {
        $blog = Blog::findOrFail($id);

        return $this->success(new BlogResource($blog), 'Blog details');
    }

    public function updateBlog($request, $id)
    {
        $blog = Blog::where('id', $id)
            ->where('type', BannerType::B2B)
            ->firstOrFail();

        $url = $request->file('image') ?
            uploadImage($request, 'image', 'blog') :
            ['url' => $blog->image, 'public_id' => $blog->public_id];

        $blog->update([
            'title' => $request->title ?? $blog->title,
            'description' => $request->description ?? $blog->description,
            'image' => $url['url'],
            'public_id' => $url['public_id'],
        ]);

        return $this->success('Details updated successfully');
    }

    public function deleteBlog($id)
    {
        $blog = Blog::where('id', $id)->where('type', BannerType::B2B)->firstOrFail();
        $blog->delete();

        return $this->success('Blog deleted successfully.');
    }

    // Shipping Agents
    public function shippingAgents()
    {
        $agents = ShippingAgent::latest()->get();

        return $this->success(ShippingAgentResource::collection($agents), 'All Agents');
    }

    public function addShippingAgent($request)
    {
        $agent = ShippingAgent::create([
            'name' => $request->name,
            'type' => $request->type,
            'country_ids' => $request->country_ids,
            'account_email' => $request->account_email,
            'account_password' => $request->account_password,
            'api_live_key' => $request->api_live_key,
            'api_test_key' => $request->api_test_key,
            'status' => $request->status,
        ]);

        return $this->success(new ShippingAgentResource($agent), 'Agent added successfully', 201);
    }

    public function viewShippingAgent($id)
    {
        $agent = ShippingAgent::findOrFail($id);

        return $this->success(new ShippingAgentResource($agent), 'Agent details');
    }

    public function editShippingAgent($request, $id)
    {
        $agent = ShippingAgent::findOrFail($id);

        $agent->update([
            'name' => $request->name ?? $agent->name,
            'type' => $request->type ?? $agent->type,
            'logo' => $request->logo ?? $agent->logo,
            'country_ids' => $request->country_ids ?? $agent->country_ids,
            'account_email' => $request->account_email ?? $agent->account_email,
            'account_password' => $request->account_password ?? $agent->account_password,
            'api_live_key' => $request->api_live_key ?? $agent->api_live_key,
            'api_test_key' => $request->api_test_key ?? $agent->api_test_key,
            'status' => $request->status ?? $agent->status,
        ]);

        return $this->success(null, 'Details updated successfully');
    }

    public function deleteShippingAgent($id)
    {
        $agent = ShippingAgent::findOrFail($id);

        $agent->delete();

        return $this->success(null, 'Details deleted successfully');
    }

    // Admin User Management
    public function adminUsers()
    {
        $authUser = userAuth();

        $searchQuery = request()->input('search');

        $admins = Admin::with('permissions:id,name')
            ->select('id', 'first_name', 'last_name', 'email', 'created_at')
            ->where('type', AdminType::B2B)
            ->whereNot('id', $authUser->id)
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                $queryBuilder->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('first_name', 'LIKE', "%{$searchQuery}%")
                        ->orWhere('email', 'LIKE', "%{$searchQuery}%");
                });
            })
            ->latest()
            ->get();

        return $this->success($admins, 'All Admin Users');
    }

    public function addAdmin($request)
    {
        DB::beginTransaction();
        try {
            $password = Str::random(5);

            $admin = Admin::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'type' => AdminType::B2B,
                'status' => AdminStatus::ACTIVE,
                'phone_number' => $request->phone_number,
                'password' => bcrypt($password),
            ]);

            $admin->permissions()->sync($request->permissions);

            $loginDetails = [
                'name' => $request->first_name,
                'email' => $request->email,
                'password' => $password,
            ];

            DB::commit();

            $type = MailingEnum::ADMIN_ACCOUNT;
            $subject = 'Admin Account Creation email';
            $mail_class = B2BNewAdminEmail::class;

            mailSend($type, $admin, $subject, $mail_class, $loginDetails);

            return $this->success($admin, 'Admin user added successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function viewAdmin($id)
    {
        $admin = Admin::where('type', AdminType::B2B)->where('id', $id)->firstOrFail();

        return $this->success($admin, 'Admin details');
    }

    public function editAdmin($request, $id)
    {
        $admin = Admin::where('type', AdminType::B2B)->where('id', $id)->firstOrFail();

        $admin->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
        ]);

        $admin->roles()->sync($request->role_id);

        if ($request->permissions) {
            $admin->permissions()->sync($request->permissions);
        }

        return $this->success($admin, 'Details updated successfully');
    }

    public function verifyPassword($request)
    {
        $currentUserId = userAuthId();

        $admin = Admin::where('type', AdminType::B2B)->where('id', $currentUserId)->firstOrFail();

        if (Hash::check($request->password, $admin->password)) {
            return $this->success(null, 'Password matched');
        }

        return $this->error(null, 'Password do not match');
    }

    public function revokeAccess($id)
    {
        $admin = Admin::where('type', AdminType::B2B)->where('id', $id)->firstOrFail();

        $admin->permissions()->detach();

        return $this->success(null, 'Access Revoked');
    }

    public function removeAdmin($id)
    {
        $admin = Admin::where('type', AdminType::B2B)->where('id', $id)->firstOrFail();

        $admin->permissions()->detach();

        $admin->delete();

        return $this->success(null, 'Deleted successfully');
    }

    // Client Logo Section
    public function allClientLogos()
    {
        $clients = ClientLogo::latest()->get();

        return $this->success(ClientLogoResource::collection($clients), 'Added client Logos');
    }

    public function addClientLogo($request)
    {
        $url = uploadImage($request, 'logo', 'clients');

        $client = ClientLogo::create([
            'name' => $request->name,
            'logo' => $url['url'],
        ]);

        return $this->success(new ClientLogoResource($client), 'Client Logo added successfully', 201);
    }

    public function getClientLogo($id)
    {
        $client = ClientLogo::findOrFail($id);

        return $this->success(new ClientLogoResource($client), 'Client details');
    }

    public function updateClientLogo($request, $id)
    {
        $client = ClientLogo::where('id', $id)->firstOrFail();

        $url = $request->file('logo') ? uploadImage($request, 'logo', 'clients') : ['url' => $client->logo];

        $client->update([
            'name' => $request->name ?? $client->name,
            'logo' => $url['url'],
        ]);

        return $this->success('Details updated successfully');
    }

    public function deleteClientLogo($id)
    {
        $client = ClientLogo::where('id', $id)->firstOrFail();

        $client->delete();

        return $this->success('Blog deleted successfully.');
    }

    // Social Links
    public function getSocialLinks()
    {
        $links = SocialSetting::latest()->get();

        return $this->success(SocialLinkResource::collection($links), 'Social links');
    }

    public function addSocialLink($request)
    {
        $link = SocialSetting::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'url' => $request->url,
        ]);

        return $this->success(new SocialLinkResource($link), 'link added successfully', 201);
    }

    public function viewLink($id)
    {
        $link = SocialSetting::findOrFail($id);

        return $this->success(new SocialLinkResource($link), 'link details');
    }

    public function editLink($request, $id)
    {
        $link = SocialSetting::findOrFail($id);

        $link->update([
            'name' => $request->name ?? $link->name,
            'icon' => $request->icon ?? $link->icon,
            'url' => $request->url ?? $link->url,
        ]);

        return $this->success(null, 'Details updated successfully');
    }

    public function deleteLink($id)
    {
        $link = SocialSetting::findOrFail($id);

        $link->delete();

        return $this->success(null, 'Link deleted successfully.');
    }
}
