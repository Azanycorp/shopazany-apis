<?php

namespace App\Services\B2B;

use App\Models\Rfq;
use App\Models\User;
use App\Models\Admin;
use App\Enum\UserType;
use App\Models\Payout;
use App\Enum\AdminType;
use App\Enum\UserStatus;
use App\Models\B2bOrder;
use App\Enum\AdminStatus;
use App\Enum\OrderStatus;
use App\Models\B2bCompany;
use App\Models\B2BProduct;
use App\Models\UserWallet;
use App\Enum\ProductStatus;
use App\Mail\AdminUserMail;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\Configuration;
use App\Mail\B2BNewAdminEmail;
use Illuminate\Support\Facades\DB;
use App\Models\B2bWithdrawalMethod;
use App\Models\BusinessInformation;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\BuyerResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\B2BSellerResource;
use App\Http\Resources\AdminB2BSellerResource;
use App\Http\Resources\B2BProductResource;
use App\Repositories\B2BProductRepository;
use App\Repositories\B2BSellerShippingRepository;

class AdminService
{
    use HttpResponse;
    protected \App\Repositories\B2BProductRepository $b2bProductRepository;
    protected \App\Repositories\B2BSellerShippingRepository $b2bSellerShippingRepository;

    public function __construct(
        B2BProductRepository $b2bProductRepository,
        B2BSellerShippingRepository $b2bSellerShippingRepository
    ) {
        $this->b2bProductRepository = $b2bProductRepository;
        $this->b2bSellerShippingRepository = $b2bSellerShippingRepository;
    }

    //dashboard
    public function dashboard()
    {
        $users =  User::all();
        $orders =  B2bOrder::orderStats();
        $rfqs =  Rfq::with(['buyer', 'seller'])->latest('id')->get();
        $completion_request =  B2bOrder::where('status', OrderStatus::SHIPPED)->take(3)->get();
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

        return $this->success($data, "Dashboard details");
    }

    public function getAllRfq()
    {
        $rfqs =  Rfq::with(['buyer', 'seller'])->latest('id')->get();
        $active_rfqs =  Rfq::where('status', OrderStatus::COMPLETED)->count();
        $users =  User::all();

        $data = [
            'buyers' => $users->where('type', UserType::B2B_BUYER)->count(),
            'sellers' => $users->where('type', UserType::B2B_SELLER)->count(),
            'active_rfqs' => $active_rfqs,
            'recent_rfqs' => $rfqs,
        ];

        return $this->success($data, "rfqs");
    }

    public function getRfqDetails($id)
    {
        $order = Rfq::with(['buyer', 'seller'])->findOrFail($id);

        return $this->success($order, "Rfq details");
    }

    public function getAllOrders()
    {
        $searchQuery = request()->input('search');
        $orders =  B2bOrder::orderStats();

        $international_orders = B2bOrder::when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
            $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                $subQuery->where('country_id', '!=', 160)
                    ->orWhere('order_no', 'LIKE', '%' . $searchQuery . '%');
            });
        })->get();

        $local_orders = B2bOrder::with(['buyer', 'seller'])->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
            $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                $subQuery->where('country_id', 160)
                    ->orWhere('order_no', 'LIKE', '%' . $searchQuery . '%');
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

        return $this->success($data, "orders");
    }

    public function getOrderDetails($id)
    {
        $order = B2bOrder::with(['buyer', 'seller'])->findOrFail($id);

        return $this->success($order, "Order details");
    }

    public function markCompleted($id)
    {
        $order = B2bOrder::findOrFail($id);
        $order->update([
            'status' => OrderStatus::DELIVERED
        ]);
        return $this->success(null, "Order Completed");
    }

    //Sellers
    //Admin section

    public function allSellers()
    {

        $sellers = User::withCount('b2bProducts')
            ->where('type', UserType::B2B_SELLER)
            ->latest('created_at')->get();

        $users = User::where('type', UserType::B2B_SELLER);
        $inactive = User::whereIn('status', [UserStatus::PENDING, UserStatus::BLOCKED, UserStatus::SUSPENDED]);

        $data = [
            'sellers_count' => $users->count(),
            'active' => $users->where('status', UserStatus::ACTIVE)->count(),
            'inactive' => $inactive->count(),
            'sellers' => $sellers,
        ];
        return $this->success($data, "sellers details");
    }

    public function approveSeller($request)
    {
        $user = User::where('type', UserType::B2B_SELLER)->findOrFail($request->user_id);

        $user->is_admin_approve = !$user->is_admin_approve;
        $user->status = $user->is_admin_approve ? 'active' : UserStatus::BLOCKED;

        $user->save();

        $status = $user->is_admin_approve ? "Approved successfully" : "Disapproved successfully";

        return $this->success(null, $status);
    }

    public function viewSeller($id)
    {
        $user = User::where('type', UserType::B2B_SELLER)->findOrFail($id);

        $search = request()->search;
        $data = new B2BSellerResource($user);
        $query = B2BProduct::with(['b2bProductImages', 'category', 'country', 'user', 'subCategory'])
            ->where('user_id', $id);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhereHas('category', function ($q) use ($search): void {
                    $q->where('name', 'like', '%' . $search . '%');
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

        $data = [
            'user_id' => $user->id
        ];

        return $this->success($data, "Updated successfully");
    }

    public function banSeller($request)
    {
        $user = User::where('type', UserType::B2B_SELLER)->findOrFail($request->user_id);

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeSeller($id)
    {
        $user = User::where('type', UserType::B2B_SELLER)->findOrFail($id);

        $user->delete();

        return $this->success(null, "User removed successfully");
    }

    public function bulkRemove($request)
    {
        $users = User::where('type', UserType::B2B_SELLER)->whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            $user->status = UserStatus::DELETED;
            $user->is_verified = 0;
            $user->is_admin_approve = 0;
            $user->save();

            $user->delete();
        }

        return $this->success(null, "User(s) have been removed successfully");
    }
    //Seller Product

    public function addSellerProduct($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }
        $parts = explode('@', $user->email);
        $name = $parts[0];
        $res = folderNames('b2bproduct', $name, 'front_image');
        $slug = Str::slug($request->name);
        if (B2BProduct::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . uniqid();
        }
        if ($request->hasFile('front_image')) {
            $path = $request->file('front_image')->store($res->frontImage, 's3');
            $url = Storage::disk('s3')->url($path);
        }
        $data = [
            'user_id' => $user->id,
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'keywords' => $request->keywords,
            'description' => $request->description,
            'front_image' => $url,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'unit_price' => $request->unit,
            'quantity' => $request->quantity,
            'availability_quantity' => $request->quantity,
            'fob_price' => $request->fob_price,
            'country_id' => is_int($user->country) ? $user->country : 160,
        ];
        $product = $this->b2bProductRepository->create($data);
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store($res->folder, 's3');
                $url = Storage::disk('s3')->url($path);

                $product->b2bProductImages()->create([
                    'image' => $url,
                ]);
            }
        }
        return $this->success(null, 'Product added successfully', 201);
    }

    public function viewSellerProduct($user_id, $product_id)
    {
        $user = User::findOrFail($user_id);
        $prod = B2BProduct::where('user_id', $user->id)->findOrFail($product_id);
        $data = new B2BProductResource($prod);

        return $this->success($data, 'Product details');
    }

    public function editSellerProduct($id, $request)
    {

        $user = User::findOrFail($request->user_id);
        $prod = B2BProduct::findOrFail($id);

        $parts = explode('@', $user->email);
        $name = $parts[0];

        $res = folderNames('b2bproduct', $name, 'front_image');

        if ($request->name) {
            $slug = Str::slug($request->name);

            if (B2BProduct::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . uniqid();
            }
        } else {
            $slug = $prod->slug;
        }

        if ($request->hasFile('front_image')) {
            $path = $request->file('front_image')->store($res->frontImage, 's3');
            $url = Storage::disk('s3')->url($path);
        } else {
            $url = $prod->front_image;
        }

        $data = [
            'user_id' => $user->id,
            'name' => $request->name ?? $prod->name,
            'slug' => $slug,
            'category_id' => $request->category_id ?? $prod->category_id,
            'sub_category_id' => $request->sub_category_id ?? $prod->name,
            'keywords' => $request->keywords ?? $prod->keywords,
            'description' => $request->description ?? $prod->description,
            'front_image' => $url,
            'minimum_order_quantity' => $request->minimum_order_quantity ?? $prod->minimum_order_quantity,
            'unit_price' => $request->unit ?? $prod->unit_price,
            'quantity' => $request->quantity ?? $prod->quantity,
            'country_id' => $user->country ?? 160,
        ];

        $product = $this->b2bProductRepository->update($id, $data);

        if ($request->hasFile('images')) {
            $product->b2bProductImages()->delete();
            foreach ($request->file('images') as $image) {
                $path = $image->store($res->folder, 's3');
                $url = Storage::disk('s3')->url($path);

                $product->b2bProductImages()->create([
                    'image' => $url,
                ]);
            }
        }

        return $this->success(null, 'Product updated successfully');
    }

    public function removeSellerProduct($user_id, $product_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $this->b2bProductRepository->delete($product_id);

        return $this->success(null, 'Deleted successfully');
    }

    public function allBuyers()
    {
        $buyers = User::with('b2bCompany')
            ->where('type', UserType::B2B_BUYER)
            ->latest('created_at')
            ->get();

        $data = [
            'buyers_count' => $buyers->count(),
            'active_buyers' => $buyers->where('status', UserStatus::ACTIVE)->count(),
            'pending_buyers' => $buyers->where('status', UserStatus::PENDING)->count(),
            'buyers' => $buyers,
        ];
        return $this->success($data, "buyers ");
    }

    public function viewBuyer($id)
    {
        $user = User::select('id', 'first_name', 'last_name', 'email', 'image')
            ->with('b2bCompany')
            ->where('type', UserType::B2B_BUYER)
            ->findOrFail($id);
        return $this->success($user, "Buyer details");
    }

    public function editBuyer($id, $data)
    {
        $user = User::findOrFail($id);
        $check = User::where('email', $data->email)->first();

        if ($check && $check->email != $user->email) {
            return $this->error(null, "Email already exist");
        }

        $image = $data->hasFile('image') ? uploadUserImage($data->file('image'), 'image', $user) : $user->image;
        $user->update([
            'first_name' => $data->first_name ?? $user->first_name,
            'last_name' => $data->last_name ?? $user->last_name,
            'email' => $data->email ?? $user->email,
            'image' => $data->image ? $image : $user->image,
        ]);
        return $this->success($user, "Buyer details");
    }

    public function editBuyerCompany($id, $data)
    {
        $user = User::findOrFail($id);
        $company = B2bCompany::where('user_id', $user->id)->first();

        if (!$company) {
            return $this->error(null, 'No company found to update', 404);
        }

        $company->update([
            'business_name' => $data->business_name ?? $company->business_name,
            'company_size' => $data->company_size ?? $company->company_size,
            'website' => $data->website ?? $company->website,
            'service_type' => $data->service_type ?? $company->service_type,
        ]);

        return $this->success($company, "company details");
    }

    public function removeBuyer($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return $this->success(null, "User removed successfully");
    }

    public function bulkRemoveBuyer($request)
    {
        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            $user->status = UserStatus::DELETED;
            $user->is_verified = 0;
            $user->is_admin_approve = 0;
            $user->save();

            $user->delete();
        }

        return $this->success(null, "User(s) have been removed successfully");
    }


    public function approveBuyer($id)
    {
        $user = User::findOrFail($id);

        $user->is_admin_approve = !$user->is_admin_approve;
        $user->status = $user->is_admin_approve ? UserStatus::ACTIVE : UserStatus::BLOCKED;

        $user->save();

        $status = $user->is_admin_approve ? "Approved successfully" : "Disapproved successfully";

        return $this->success(null, $status);
    }

    public function banBuyer($id)
    {
        $user = User::findOrFail($id);

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    //CMS / Promo and banners
    public function adminProfile()
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminType::B2B)->findOrFail($authUser->id);
        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function updateAdminProfile($data)
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminType::B2B)->findOrFail($authUser->id);
        $user->update([
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'phone_number' => $data->phone_number,
        ]);
        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function enableTwoFactor($data)
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminType::B2B)->findOrFail($authUser->id);
        $user->update([
            'two_factor_enabled' => $data->two_factor_enabled,
        ]);

        return $this->success('Settings updated');
    }

    public function updateAdminPassword($data)
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminType::B2B)->findOrFail($authUser->id);
        $user->update([
            'password' => Hash::make($data->password),
        ]);
        return $this->success(null, 'Password updated');
    }

    public function getConfigDetails()
    {
        $config = Configuration::first();
        return $this->success($config, 'Config details');
    }

    public function updateConfigDetails($data)
    {
        $configData = [
            'usd_rate' => $data->usd_rate,
            'company_profit' => $data->company_profit,
            'email_verify' => $data->email_verify,
            'currency_code' => $data->currency_code,
            'currency_symbol' => $data->currency_symbol,
            'promotion_start_date' => $data->promotion_start_date,
            'promotion_end_date' => $data->promotion_end_date,
            'min_deposit' => $data->min_deposit,
            'max_deposit' => $data->max_deposit,
            'min_withdrawal' => $data->min_withdrawal,
            'withdrawal_frequency' => $data->withdrawal_frequency,
            'withdrawal_status' => $data->withdrawal_status,
            'max_withdrawal' => $data->max_withdrawal,
            'withdrawal_fee' => $data->withdrawal_fee,
            'seller_perc' => $data->seller_perc,
            'paystack_perc' => $data->paystack_perc,
            'paystack_fixed' => $data->paystack_fixed,
        ];

        $config = Configuration::first();

        if ($config) {
            $config->update($configData);
        } else {
            Configuration::create($configData);
        }

        return $this->success(null, 'Details updated');
    }

    //seller withdrawal request
    public function widthrawalRequests()
    {
        $payouts =  Payout::select(['id', 'seller_id', 'amount', 'status', 'created_at'])
            ->with(['user' => function ($query): void {
                $query->select('id', 'first_name', 'last_name')->where('type', UserType::B2B_SELLER);
            }])->latest('id')->get();

        if ($payouts->isEmpty()) {
            return $this->error(null, 'No record found');
        }

        return $this->success($payouts, 'Withdrawal requests');
    }

    public function viewWidthrawalRequest($id)
    {
        $payout =  Payout::with(['user' => function ($query): void {
            $query->select('id', 'first_name', 'last_name')->where('type', UserType::B2B_SELLER);
        }])->findOrFail($id);

        return $this->success($payout, 'request details');
    }

    public function approveWidthrawalRequest($id)
    {
        $payout =  Payout::findOrFail($id);
        $payout->update([
            'status' => 'paid',
            'date_paid' => now()->toDateString(),
        ]);

        return $this->success(null, 'request Approved successfully');
    }

    public function cancelWidthrawalRequest($id)
    {
        $payout =  Payout::findOrFail($id);
        $wallet = UserWallet::where('user_id', $payout->seller_id)->first();

        if (!$wallet) {
            return $this->error(null, 'No account found');
        }

        DB::beginTransaction();

        try {
            $wallet->master_wallet += $payout->amount;
            $wallet->save();

            $payout->update([
                'status' => 'cancelled',
            ]);

            DB::commit();

            return $this->success(null, 'request cancelled');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }

    //seller withdrawal method request
    public function widthrawalMethods()
    {
        $accounts =  B2bWithdrawalMethod::where('status', 'pending')->latest('id')->get();

        if ($accounts->isEmpty()) {
            return $this->error(null, 'No record found', 404);
        }

        return $this->success($accounts, 'Withdrawal methods');
    }

    public function viewWidthrawalMethod($id)
    {
        $account =  B2bWithdrawalMethod::with('user.businessInformation')->findOrFail($id);

        $business = BusinessInformation::select(['business_location', 'business_type', 'business_name', 'business_reg_number', 'business_phone', 'business_reg_document', 'identification_type_document', 'user_id'])->with(['user' => function ($query): void {
            $query->where('type', UserType::B2B_SELLER)->select('id', 'first_name', 'last_name');
        }])->where('user_id', $account->user_id)->first();

        if ($account->isEmpty()) {
            return $this->error(null, 'No record found', 404);
        }

        $data = [
            'account_info' => $account,
            'business_info' => $business,
        ];

        return $this->success($data, 'request details');
    }

    public function approveWidthrawalMethod($id)
    {
        $account =  B2bWithdrawalMethod::findOrFail($id);

        $account->update([
            'status' => 'active',
        ]);

        return $this->success(null, 'Account Approved');
    }

    public function rejectWidthrawalMethod($id, $data)
    {
        $account =  B2bWithdrawalMethod::findOrFail($id);

        $account->update([
            'status' => ProductStatus::DECLINED,
            'admin_comment' => $data->note
        ]);

        return $this->success(null, 'Comment Submitted successfully');
    }

    //seller Product request
    public function allProducts()
    {
        $products =  B2BProduct::with(['user' => function ($query): void {
            $query->select('id', 'first_name', 'last_name')->where('type', UserType::B2B_SELLER);
        }])->where('status', OrderStatus::PENDING)->latest('id')->get();

        if ($products->isEmpty()) {
            return $this->error(null, 'No record found', 404);
        }
        return $this->success($products, 'Products listing');
    }

    public function viewProduct($id)
    {
        $product = B2BProduct::with(['user' => function ($query): void {
            $query->select('id', 'first_name', 'last_name')->where('type', UserType::B2B_SELLER);
        }])->findOrFail($id);

        return $this->success($product, 'Product details');
    }

    public function approveProduct($id)
    {
        $product =  B2BProduct::findOrFail($id);

        $product->update([
            'status' => ProductStatus::ACTIVE,
        ]);

        return $this->success(null, 'Product Approved');
    }

    public function rejectProduct($id, $data)
    {
        $product = B2BProduct::findOrFail($id);
        $product->update([
            'status' => ProductStatus::DECLINED,
            'admin_comment' => $data->note
        ]);

        return $this->success(null, 'Comment Submitted successfully');
    }


    //Admin User Management
    public function adminUsers()
    {
        $searchQuery = request()->input('search');
        $users =  User::all();

        $admins = Admin::with(['permissions' => function ($query): void {
            $query->select('permission_id', 'name');
        }])->select('id', 'first_name', 'last_name', 'email', 'created_at')
            ->latest('created_at')->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
                $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                    $subQuery->where('type', AdminType::B2B)
                        ->orWhere('first_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchQuery . '%');
                });
            })->get();

        $data = [
            'buyers' => $users->where('type', UserType::B2B_BUYER)->count(),
            'sellers' => $users->where('type', UserType::B2B_SELLER)->count(),
            'pending_approval' => $users->where('status', UserStatus::PENDING)->count(),
            'admin_users' => $admins,
        ];
        return $this->success($data, 'All Admin Users');
    }

    public function addAdmin($data)
    {

        DB::beginTransaction();
        try {
            $password = generateRandomString();
            $admin = Admin::create([
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'email' => $data->email,
                'type' => AdminType::B2B,
                'status' => AdminStatus::ACTIVE,
                'phone_number' => $data->phone_number,
                'password' => bcrypt($data->password),
            ]);
            $admin->permissions()->sync($data->permissions);
            $loginDetails = [
                'name' => $data->first_name,
                'email' => $data->email,
                'password' => $password,
            ];
            DB::commit();

            send_email($data->email, new B2BNewAdminEmail($loginDetails));

            return $this->success($admin, 'Admin user added successfully', 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function viewAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        return $this->success($admin, 'Admin details');
    }

    public function editAdmin($id, $data)
    {
        $admin = Admin::findOrFail($id);
        $admin->update([
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'phone_number' => $data->phone_number,
        ]);
        $admin->roles()->sync($data->role_id);
        if ($data->permissions) {
            $admin->permissions()->sync($data->permissions);
        }
        return $this->success($admin, 'Details updated successfully');
    }

    public function verifyPassword($data)
    {
        $currentUserId = userAuthId();
        $admin = Admin::findOrFail($currentUserId);
        if (Hash::check($data->password, $admin->password)) {
            return $this->success(null, 'Password matched');
        }
        return $this->error(null, 'Password do not match');
    }
    public function revokeAccess($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->permissions()->detach();
        return $this->success(null, 'Access Revoked');
    }

    public function removeAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->permissions()->detach();
        $admin->delete();

        return $this->success(null, 'Deleted successfully');
    }
}
