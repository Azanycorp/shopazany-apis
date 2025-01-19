<?php

namespace App\Services\B2B;

use Carbon\Carbon;
use App\Models\Rfq;
use App\Models\User;
use App\Models\Admin;
use App\Enum\UserType;
use App\Models\Payout;
use App\Enum\UserStatus;
use App\Models\B2bOrder;
use App\Models\B2bQuote;
use App\Enum\AdminStatus;
use App\Enum\OrderStatus;
use App\Models\B2BProduct;
use App\Models\UserWallet;
use App\Enum\ProductStatus;
use App\Models\B2bWishList;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\Configuration;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use Illuminate\Support\Facades\DB;
use App\Models\B2bWithdrawalMethod;
use App\Models\BusinessInformation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\SellerResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\B2BSellerResource;
use App\Http\Resources\B2BProductResource;
use App\Repositories\B2BProductRepository;
use App\Http\Resources\SellerProfileResource;
use App\Repositories\B2BSellerShippingRepository;

class AdminService
{
    use HttpResponse;
    protected $b2bProductRepository;
    protected $b2bSellerShippingRepository;

    public function __construct(
        B2BProductRepository $b2bProductRepository,
        B2BSellerShippingRepository $b2bSellerShippingRepository
    ) {
        $this->b2bProductRepository = $b2bProductRepository;
        $this->b2bSellerShippingRepository = $b2bSellerShippingRepository;
    }
    //Rfq

    public function getAllRfq()
    {
        $rfqs =  Rfq::with(['buyer', 'seller'])->whereIn('status', [OrderStatus::PENDING, OrderStatus::REVIEW, OrderStatus::INPROGRESS])->get();

        if (count($rfqs) < 1) {
            return $this->error(null, "No record found.", 404);
        }

        return $this->success($rfqs, "rfqs");
    }

    public function getRfqDetails($id)
    {
        $order = Rfq::with(['buyer', 'seller'])->find($id);
        if (!$order) {
            return $this->error(null, "No record found.", 404);
        }

        return $this->success($order, "Rfq details");
    }
    //Orders Completed Rfq

    public function getAllOrders()
    {
        $rfqs =  Rfq::with(['buyer', 'seller'])->whereIn('status', [OrderStatus::DELIVERED, OrderStatus::SHIPPED])->get();

        if (count($rfqs) < 1) {
            return $this->error(null, "No record found.", 404);
        }

        return $this->success($rfqs, "orders");
    }

    public function getOrderDetails($id)
    {
        $order = Rfq::with(['buyer', 'seller'])->find($id);

        if (!$order) {
            return $this->error(null, "No record found.", 404);
        }

        return $this->success($order, "Order details");
    }

    //Sellers
    //Admin section

    public function allSellers()
    {
        $searchQuery = request()->input('search');
        $approvedQuery = request()->query('approved');
        $total_users = User::where('type', UserType::B2B_SELLER);
        $inactive_users = User::where(['type' => UserType::B2B_SELLER, 'status' => UserStatus::PENDING])->count()
            + User::where(['type' => UserType::B2B_SELLER, 'status' => UserStatus::SUSPENDED])->count()
            + User::where(['type' => UserType::B2B_SELLER, 'status' => UserStatus::BLOCKED])->count();

        $users = User::with(['businessInformation'])
            ->where('type', UserType::B2B_SELLER)
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                $queryBuilder->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('first_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('middlename', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchQuery . '%');
                });
            })
            ->when($approvedQuery !== null, function ($queryBuilder) use ($approvedQuery) {
                $queryBuilder->where('is_admin_approve', $approvedQuery);
            })
            ->paginate(25);

        $data = B2BSellerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'Sellers filtered',
            'all_users' => $total_users->count(),
            'active_users' => $total_users->where('status', UserStatus::ACTIVE)->count(),
            'inactive_users' => $inactive_users,
            'data' => $data,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
            ],
        ];
    }

    public function approveSeller($request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->is_admin_approve = !$user->is_admin_approve;
        $user->status = $user->is_admin_approve ? 'active' : UserStatus::BLOCKED;

        $user->save();

        $status = $user->is_admin_approve ? "Approved successfully" : "Disapproved successfully";

        return $this->success(null, $status);
    }

    public function viewSeller($id)
    {
        $user = User::where('type', UserType::B2B_SELLER)
            ->find($id);

        if (!$user) {
            return $this->error(null, "Seller not found", 404);
        }
        $search = request()->search;
        $data = new B2BSellerResource($user);
        // $products = B2BProduct::whereBelongsTo($user)->latest('id')->get();
        $query = B2BProduct::with(['b2bProductImages', 'category', 'country', 'user', 'subCategory'])
            ->where('user_id', $id);

        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhereHas('category', function ($q) use ($search) {
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
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

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
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeSeller($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->delete();

        return $this->success(null, "User removed successfully");
    }

    public function bulkRemove($request)
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
    //Seller Product

    public function addSellerProduct($request)
    {
        $user = User::find($request->user_id);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }

        try {

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

            $data = (array)[
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
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function viewSellerProduct($product_id)
    {
        $prod = $this->b2bProductRepository->find($product_id);
        $data = new B2BProductResource($prod);

        return $this->success($data, 'Product details');
    }

    public function editSellerProduct($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::findOrFail($request->user_id);
        $prod = B2BProduct::findOrFail($request->product_id);

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

        $data = (array)[
            'user_id' => $user->id,
            'name' => $request->name ?? $prod->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'keywords' => $request->keywords,
            'description' => $request->description,
            'front_image' => $url,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'unit_price' => $request->unit,
            'quantity' => $request->quantity,
            'available_quantity' => $request->quantity - $prod->sold,
            'fob_price' => $request->fob_price,
            'country_id' => $user->country ?? 160,
        ];

        $product = $this->b2bProductRepository->update($request->product_id, $data);

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

    //Sellers
    //Admin section

    public function allBuyers()
    {
        $searchQuery = request()->input('search');
        $approvedQuery = request()->query('approved');
        $total_users = User::where('type', UserType::B2B_BUYER);
        $inactive_users = User::where(['type' => UserType::B2B_BUYER, 'status' => UserStatus::PENDING])->count()
            + User::where(['type' => UserType::B2B_BUYER, 'status' => UserStatus::SUSPENDED])->count()
            + User::where(['type' => UserType::B2B_BUYER, 'status' => UserStatus::BLOCKED])->count();

        $users = User::with(['businessInformation'])
            ->where('type', UserType::B2B_BUYER)
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                $queryBuilder->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('first_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('last_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('middlename', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchQuery . '%');
                });
            })
            ->when($approvedQuery !== null, function ($queryBuilder) use ($approvedQuery) {
                $queryBuilder->where('is_admin_approve', $approvedQuery);
            })
            ->paginate(25);

        // $data = B2BBuyerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'Buyers filtered',
            'all_users' => $total_users->count(),
            'active_users' => $total_users->where('status', UserStatus::ACTIVE)->count(),
            'inactive_users' => $inactive_users,
            'data' => $users,
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'prev_page_url' => $users->previousPageUrl(),
                'next_page_url' => $users->nextPageUrl(),
            ],
        ];
    }

    public function viewBuyer($id)
    {
        $user = User::select('id', 'first_name', 'last_name', 'email', 'image')->with('b2bCompany')->where('type', UserType::B2B_BUYER)
            ->find($id);

        if (!$user) {
            return $this->error(null, "Buyer not found", 404);
        }

        return [
            'status' => 'true',
            'message' => 'Buyer details',
            'data' => $user,
        ];
    }

    public function removeBuyer($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

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
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->is_admin_approve = !$user->is_admin_approve;
        $user->status = $user->is_admin_approve ? UserStatus::ACTIVE : UserStatus::BLOCKED;

        $user->save();

        $status = $user->is_admin_approve ? "Approved successfully" : "Disapproved successfully";

        return $this->success(null, $status);
    }

    public function banBuyer($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    //CMS / Promo and banners

    public function adminProfile()
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminStatus::B2B)->findOrFail($authUser->id);
        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function updateAdminProfile($data)
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminStatus::B2B)->findOrFail($authUser->id);
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
        $user = Admin::where('type', AdminStatus::B2B)->findOrFail($authUser->id);
        $user->update([
            'two_factor_enabled' => $data->two_factor_enabled,
        ]);

        return $this->success('Settings updated');
    }

    public function updateAdminPassword($data)
    {
        $authUser = userAuth();
        $user = Admin::where('type', AdminStatus::B2B)->findOrFail($authUser->id);
        $user->update([
            'password' => Hash::make($data->password),
        ]);
        $data = new AdminUserResource($user);

        return $this->success(null, 'Password updated');
    }

    public function getConfigDetails()
    {
        $config = Configuration::first();
        return $this->success($config, 'Config details');
    }

    public function updateConfigDetails($data)
    {
        $config = Configuration::first();
        if ($config) {
            $config->update([
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
                'max_withdrawal' => $data->max_withdrawal,
                'withdrawal_fee' => $data->withdrawal_fee,
                'seller_perc' => $data->seller_perc,
                'paystack_perc' => $data->paystack_perc,
                'paystack_fixed' => $data->paystack_fixed,
            ]);
        }
        $config = Configuration::create([
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
            'max_withdrawal' => $data->max_withdrawal,
            'withdrawal_fee' => $data->withdrawal_fee,
            'seller_perc' => $data->seller_perc,
            'paystack_perc' => $data->paystack_perc,
            'paystack_fixed' => $data->paystack_fixed,
        ]);
        return $this->success(null, 'Details updated');
    }

    //seller withdrawal request
    public function widthrawalRequests()
    {
        $payouts =  Payout::select(['id', 'seller_id', 'amount', 'status', 'created_at'])->with(['user' => function ($query) {
            $query->where('type', UserType::B2B_SELLER)->select('id', 'first_name', 'last_name');
        }])->latest('id')->get();
        if (count($payouts) < 1) {
            return $this->error(null, 'No record found');
        }
        return $this->success($payouts, 'Withdrawal requests');
    }

    public function viewWidthrawalRequest($id)
    {
        $payout =  Payout::with(['user' => function ($query) {
            $query->where('type', UserType::B2B_SELLER)->select('id', 'first_name', 'last_name');
        }])->find($id);
        if (!$payout) {
            return $this->error(null, 'No record found');
        }
        return $this->success($payout, 'request details');
    }

    public function approveWidthrawalRequest($id)
    {
        $payout =  Payout::find($id);
        if (!$payout) {
            return $this->error(null, 'No record found');
        }

        DB::beginTransaction();

        try {
            $payout->update([
                'status' => 'paid',
                'date_paid' => now()->toDateString(),
            ]);

            DB::commit();
            return $this->success(null, 'request Approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }

    public function cancelWidthrawalRequest($id)
    {
        $payout =  Payout::find($id);
        if (!$payout) {
            return $this->error(null, 'No record found');
        }
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
        if (count($accounts) < 1) {
            return $this->error(null, 'No record found');
        }
        return $this->success($accounts, 'Withdrawal methods');
    }

    public function viewWidthrawalMethod($id)
    {
        $account =  B2bWithdrawalMethod::with('user.businessInformation')->find($id);
        $business = BusinessInformation::select(['business_location', 'business_type', 'business_name', 'business_reg_number', 'business_phone', 'business_reg_document', 'identification_type_document', 'user_id'])->with(['user' => function ($query) {
            $query->where('type', UserType::B2B_SELLER)->select('id', 'first_name', 'last_name');
        }])->where('user_id', $account->user_id)->first();
        if (!$account) {
            return $this->error(null, 'No record found');
        }
        $data = [
            'account_info' => $account,
            'business_info' => $business,
        ];
        return $this->success($data, 'request details');
    }

    public function approveWidthrawalMethod($id)
    {
        $account =  B2bWithdrawalMethod::find($id);
        if (!$account) {
            return $this->error(null, 'No record found');
        }

        $account->update([
            'status' => 'active',
        ]);
        return $this->success(null, 'Account Approved');
    }

    public function rejectWidthrawalMethod($data)
    {
        $account =  B2bWithdrawalMethod::find($data->account_id);
        if (!$account) {
            return $this->error(null, 'No record found');
        }
        $account->update([
            'status' => 'declined',
            'admin_comment' => $data->note
        ]);
        return $this->success(null, 'Comment Submitted successfully');
    }

    //seller Product request
    public function allProducts()
    {
        $products =  B2BProduct::with(['user' => function ($query) {
            $query->where('type', UserType::B2B_SELLER)->select('id', 'first_name', 'last_name');
        }])->where('status', 'pending')->latest('id')->get();
        if (count($products) < 1) {
            return $this->error(null, 'No record found');
        }
        return $this->success($products, 'Products listing');
    }

    public function viewProduct($id)
    {
        $product = B2BProduct::with(['user' => function ($query) {
            $query->where('type', UserType::B2B_SELLER)->select('id', 'first_name', 'last_name');
        }])->find($id);
        if (!$product) {
            return $this->error(null, 'No record found');
        }
        return $this->success($product, 'Product details');
    }

    public function approveProduct($id)
    {
        $product =  B2BProduct::find($id);
        if (!$product) {
            return $this->error(null, 'No record found');
        }

        $product->update([
            'status' => 'active',
        ]);
        return $this->success(null, 'Product Approved');
    }

    public function rejectProduct($data)
    {
        $product = B2BProduct::find($data->product_id);
        if (!$product) {
            return $this->error(null, 'No record found');
        }

        $product->update([
            'status' => 'declined',
            'admin_comment' => $data->note
        ]);
        return $this->success(null, 'Comment Submitted successfully');
    }
}
