<?php

namespace App\Services\B2B;

use App\Models\Rfq;
use App\Models\User;
use App\Models\Payout;
use App\Models\B2bOrder;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Models\B2BProduct;
use App\Models\RfqMessage;
use App\Models\UserWallet;
use App\Mail\B2BOrderEmail;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\Configuration;
use App\Enum\WithdrawalStatus;
use App\Imports\ProductImport;
use App\Models\B2bOrderRating;
use Illuminate\Support\Carbon;
use App\Models\B2bOrderFeedback;
use App\Imports\B2BProductImport;
use Illuminate\Support\Facades\DB;
use App\Models\B2bWithdrawalMethod;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\B2BSellerShippingAddress;
use App\Http\Resources\B2BProductResource;
use App\Repositories\B2BProductRepository;
use App\Http\Resources\SellerProfileResource;
use App\Repositories\B2BSellerShippingRepository;
use App\Http\Resources\B2BSellerShippingAddressResource;

class SellerService extends Controller
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

    public function businessInformation($request)
    {
        $user = User::with('businessInformation')->findOrFail($request->user_id);
        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'country' => $request->country_id,
        ]);
        $folder = folderName('document/businessreg');
        $businessDoc = $request->hasFile('business_reg_document') ? uploadImage($request, 'business_reg_document', $folder) : null;
        $identifyTypeDoc = null;
        if ($request->identification_type && $request->hasFile('identification_type_document')) {
            $fld = folderName('document/identifytype');
            $identifyTypeDoc = uploadImage($request, 'identification_type_document', $fld);
        }
        $user->businessInformation()->create([
            'business_location' => $request->business_location,
            'business_type' => $request->business_type,
            'business_name' => $request->business_name,
            'business_reg_number' => $request->business_reg_number,
            'business_phone' => $request->business_phone,
            'country_id' => $request->country_id,
            'city' => $request->city,
            'address' => $request->address,
            'zip' => $request->zip,
            'state' => $request->state,
            'apartment' => $request->apartment,
            'business_reg_document' => $businessDoc,
            'identification_type' => $request->identification_type,
            'identification_type_document' => $identifyTypeDoc,
            'agree' => $request->agree,
        ]);
        return $this->success(null, 'Created successfully');
    }

    public function profile()
    {
        $auth = userAuth();

        $user = User::findOrFail($auth->id);
        $data = new SellerProfileResource($user);

        return $this->success($data, 'Seller profile');
    }

    public function editAccount($request)
    {
        $user = User::findOrFail($request->user_id);

        $image = $request->hasFile('logo') ? uploadUserImage($request, 'logo', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'middlename' => $request->middlename ?? $user->middlename,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'image' => $image
        ]);

        return $this->success(null, "Updated successfully");
    }

    public function changePassword($request)
    {
        $user = $request->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
            return $this->success(null, 'Password Successfully Updated');
        }
        return $this->error(null, 'Old Password did not match', 422);
    }

    public function editCompany($request)
    {
        $user = User::with('businessInformation')->findOrFail($request->user_id);
        $image = '';
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store($request->logo, 's3');
            $url = Storage::disk('s3')->url($path);
            $image = $request->hasFile('logo') ? $url : $user->businessInformation->logo;
        }

        $user->businessInformation()->update([
            'business_name' => $request->business_name,
            // 'business_reg_number' => $request->business_reg_number,
            'business_phone' => $request->business_phone,
            'country_id' => $request->country_id,
            'city' => $request->city,
            'zip' => $request->postal_code,
            'address' => $request->address,
            'state' => $request->state,
            'logo' => $image,
        ]);

        return $this->success(null, "Updated successfully");
    }

    public function exportSellerProduct($userId, $data)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        switch ($data->type) {
            case 'product':
                return $this->exportB2bProduct($userId, $data);

            case 'order':
                return "None yet";

            default:
                return "Type not found";
        }
    }

    public function b2bproductImport($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $seller = auth()->user();

        try {
            Excel::import(new B2BProductImport($seller), $request->file('file'));

            return $this->success(null, "Imported successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
    public function addProduct($request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
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
                'default_currency' => $user->default_currency,
                'fob_price' => $request->fob_price,
                'status' => 'active',
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
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function getAllProduct($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::select('id')->findOrFail($request->user_id)->id;
        $search = request()->input('search');

        $products = $this->b2bProductRepository->all($user, $search);
        $data = B2BProductResource::collection($products);

        return $this->success($data, 'All products');
    }

    public function getProductById($product_id, $user_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $prod = $this->b2bProductRepository->find($product_id);
        $data = new B2BProductResource($prod);

        return $this->success($data, 'Product details');
    }

    public function updateProduct($request)
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

        $data = [
            'user_id' => $user->id,
            'name' => $request->name ?? $prod->name,
            'slug' => $slug,
            'category_id' => $request->category_id ?? $prod->category_id,
            'sub_category_id' => $request->sub_category_id ?? $prod->sub_category_id,
            'keywords' => $request->keywords ?? $prod->keywords,
            'description' => $request->description ?? $prod->description,
            'front_image' => $url,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'unit_price' => $request->unit,
            'quantity' => $request->quantity,
            'default_currency' => $user->default_currency,
            'availability_quantity' => $request->quantity - $prod->sold,
            'fob_price' => $request->fob_price ?? $prod->fob_price ?? 0,
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

    public function deleteProduct($user_id, $product_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }


        $this->b2bProductRepository->delete($product_id);

        return $this->success(null, 'Deleted successfully');
    }

    public function getAnalytics($user_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with(['b2bProducts.category'])
            ->withCount(['b2bProducts', 'b2bProducts as category_count' => function ($query): void {
                $query->distinct('category_id');
            }])
            ->findOrFail($user_id);

        $data = [
            'product_count' => $user->b2b_products_count,
            'category_count' => $user->category_count
        ];

        return $this->success($data, 'Analytics');
    }

    public function addShipping($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('b2bSellerShippingAddresses')->find($request->user_id);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }

        $data = [
            'user_id' => $request->user_id,
            'address_name' => $request->address_name,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
        ];

        $this->b2bSellerShippingRepository->create($data);

        return $this->success(null, 'Added successfully');
    }

    public function getAllShipping($user_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $address = $this->b2bSellerShippingRepository->all($user_id);
        $data = B2BSellerShippingAddressResource::collection($address);

        return $this->success($data, 'All address');
    }

    public function getShippingById($user_id, $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $shipping = $this->b2bSellerShippingRepository->find($shipping_id);
        $data = new B2BSellerShippingAddressResource($shipping);

        return $this->success($data, 'Address detail');
    }

    public function updateShipping($request, $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $data = [
            'address_name' => $request->address_name,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'street' => $request->street,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'state_id' => $request->state_id,
            'country_id' => $request->country_id,
        ];

        $this->b2bSellerShippingRepository->update($shipping_id, $data);

        return $this->success(null, 'Updated successfully');
    }

    public function deleteShipping($user_id, $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $this->b2bSellerShippingRepository->delete($shipping_id);

        return $this->success(null, 'Deleted successfully');
    }

    public function setDefault($user_id, $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $shipping = B2BSellerShippingAddress::where('user_id', $user_id)
            ->where('id', $shipping_id)
            ->firstOrFail();

        if ($shipping->is_default) {
            return $this->error(null, 'Already set at default', 400);
        }

        B2BSellerShippingAddress::where('user_id', $user_id)->update(['is_default' => 0]);

        $shipping->update([
            'is_default' => 1
        ]);

        return $this->success(null, 'Set successfully');
    }

    public function getComplaints($user_id)
    {
        $user = User::with(['b2bProducts.b2bRequestRefunds'])->findOrFail($user_id);

        $refunds = $user->b2bProducts->flatMap(function ($product) {
            return $product->b2bRequestRefunds;
        });

        if ($complaintNumber = request()->query('complaint_number')) {
            $refunds = $refunds->where('complaint_number', $complaintNumber);
        }

        if ($type = request()->query('type')) {
            $refunds = $refunds->where('type', $type);
        }

        if ($status = request()->query('status')) {
            $refunds = $refunds->where('status', $status);
        }

        if ($fromDate = request()->query('from') && $toDate = request()->query('to')) {
            $refunds = $refunds->whereBetween('created_at', [$fromDate, $toDate]);
        }

        if ($orderNo = request()->query('order_number')) {
            return $refunds->where('order_number', $orderNo);
        }

        return $refunds;
    }

    public function getTemplate()
    {
        $data = getB2BProductTemplate();

        return $this->success($data, "Product template");
    }

    public function productImport($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $seller = userAuth();

        try {
            Excel::import(new ProductImport($seller), $request->file('file'));

            return $this->success(null, "Imported successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function export($userId, $data)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        switch ($data->type) {
            case 'product':
                return $this->b2bExportProduct($userId);

            case 'order':
                return "None yet";

            default:
                return "Type not found";
        }
    }

    public function getEarningReport()
    {
        $currentUserId = userAuthId();
        if (!$currentUserId) {
            return $this->error(null, "Unauthorized action.", 401);
        }
        $startDate = now()->subDays(30); // 30 days ago
        $endDate = now(); // Current date and time

        $currentUserId = userAuthId();
        $totalSales =  Rfq::where('seller_id', $currentUserId)
            ->where('status', 'delivered')
            ->sum('total_amount');

        $payouts =  Payout::where('seller_id', $currentUserId)->get();

        //payouts this month
        $monthlyPayout =  Payout::where([
            'seller_id' => $currentUserId,
            'status' => 'paid'
        ])->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        //order this month
        $monthlyOrder =  Rfq::where([
            'seller_id' => $currentUserId,
            'status' => 'delivered'
        ])->whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');

        $data = (object) [
            'total_sales_alltime' => $totalSales,
            'sales_this_month' => $monthlyOrder,
            'total_payout' => $payouts->where('status', 'paid')->sum('amount'),
            'payout_this_month' => $monthlyPayout,
            'total_category' => 0,
            'total_brand' => 0,
        ];
        return $this->success($data, "Earning details");
    }



    public function getOrderDetails($id)
    {
        $order = B2bOrder::find($id);
        if (!$order) {
            return $this->error(null, "No record found.", 404);
        }

        return $this->success($order, 'order details');
    }

    //Rfq
    public function getAllRfq()
    {
        $currentUserId = userAuthId();

        $rfqs = Rfq::with('buyer')
            ->where('seller_id', $currentUserId)
            ->latest('id')
            ->get();

        $orders = B2bOrder::with('buyer')
            ->where('seller_id', $currentUserId)
            ->latest('id')
            ->get();


        $data = [
            'total_rfqs' => $rfqs->count(),
            'rfqs' => $rfqs,
            'total_orders' => $orders->count(),
            'orders' => $orders,
        ];

        return $this->success($data, "orders");
    }

    public function getRfqDetails($id)
    {
        $rfq = Rfq::find($id);
        if (!$rfq) {
            return $this->error(null, "No record found.", 404);
        }
        $messages = RfqMessage::with(['seller', 'buyer'])->where('rfq_id', $rfq->id)->get();
        $data = [
            'rfq' => $rfq,
            'messages' => $messages
        ];
        return $this->success($data, 'rfq details');
    }

    public function markShipped($data)
    {
        $rfq = Rfq::find($data->rfq_id);

        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }

        $rfq->update([
            'payment_status' => 'paid',
            'status' => 'shipped',
            'shipped_date' => now()->toDateString(),
        ]);

        return $this->success($rfq, 'Product Shipped successfully');
    }

    public function replyRequest($data)
    {
        $rfq = Rfq::find($data->rfq_id);


        if (!$rfq) {
            return $this->error(null, "No record found details", 404);
        }

        $amount = ($data->preferred_unit_price * $rfq->product_quantity);

        $message = RfqMessage::create([
            'rfq_id' => $rfq->id,
            'seller_id' => userAuthId(),
            'p_unit_price' => $data->preferred_unit_price,
            'note' => $data->note
        ]);

        $rfq->update([
            'p_unit_price' => $data->preferred_unit_price,
            'total_amount' => $amount
        ]);

        return $this->success($message, "message details");
    }

    public function markDelivered($data)
    {
        $rfq = Rfq::find($data->rfq_id);

        if (! $rfq) {
            return $this->error(null, 'No record found to send', 404);
        }

        $rfq->update([
            'payment_status' => 'paid',
            'status' => 'delivered',
            'delivery_date' => now()->toDateString()
        ]);

        //Update product
        $product = B2BProduct::findOrFail($rfq->product_id);
        $remaining_qty = $product->quantity - $rfq->product_quantity;

        $product->update([
            'availability_quantity' => $remaining_qty,
            'sold' => $product->sold + $rfq->product_quantity,
        ]);

        return $this->success($rfq, 'Product marked delivered successfully');
    }

    public function confirmPayment($data)
    {
        $rfq = Rfq::findOrFail($data->rfq_id);
        $seller = User::select('id')->findOrFail($rfq->seller_id);
        $buyer = User::select('id', 'email', 'first_name', 'last_name')->findOrFail($rfq->buyer_id);
        $product = B2BProduct::select(['id', 'name', 'front_image', 'quantity', 'sold'])->findOrFail($rfq->product_id);

        DB::beginTransaction();

        try {
            $amount = $rfq->total_amount;
            $order = B2bOrder::create([
                'buyer_id' => $rfq->buyer_id,
                'seller_id' => $rfq->seller_id,
                'product_id' => $rfq->product_id,
                'product_quantity' => $rfq->product_quantity,
                'order_no' => 'ORD-' . now()->timestamp . '-' . Str::random(8),
                'product_data' => $product,
                'total_amount' => $amount,
                'payment_method' => PaymentType::OFFLINE,
                'payment_status' => OrderStatus::PAID,
                'status' => OrderStatus::PENDING,
            ]);

            $orderedItems = [
                'product_name' => $product->name,
                'image' => $product->front_image,
                'quantity' => $rfq->product_quantity,
                'price' => $rfq->total_amount,
                'buyer_name' => $buyer->first_name . ' ' . $buyer->last_name,
                'order_number' => $order->order_no,
            ];

            $product->quantity -= $rfq->product_quantity;
            $product->sold += $rfq->product_quantity;
            $product->save();

            $config = Configuration::first();
            $sellerPercentage = $config->seller_perc ?? 0;
            $credit = ($sellerPercentage / 100) * $amount;

            if ($credit > 0) {
                $wallet = UserWallet::firstOrNew(['seller_id' => $seller->id]);

                $wallet->master_wallet = ($wallet->master_wallet ?? 0) + $credit;
                $wallet->save();
            }

            $rfq->delete();

            DB::commit();
            send_email($buyer->email, new B2BOrderEmail($orderedItems));
            return $this->success($order, 'Payment Confirmed successfully');
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function rateOrder($data)
    {
        $rfq = Rfq::find($data->rfq_id);

        $userId = userAuthId();

        if (!$rfq) {
            return $this->error(null, "No record found", 404);
        }

        B2bOrderRating::create([
            'seller_id' => $userId,
            'order_no' => $rfq->quote_no,
            'rating' => $data->rating,
            'description' => $data->description ? $data->description : 'description'
        ]);

        return $this->success(null, "Rating successful");
    }

    public function orderFeeback($data)
    {
        $rfq = Rfq::find($data->rfq_id);
        $userId = userAuthId();

        if (!$rfq) {
            return $this->error(null, "No record found", 404);
        }

        B2bOrderFeedback::create([
            'seller_id' => $userId,
            'order_no' => $rfq->quote_no,
            'description' => $data->description
        ]);

        return $this->success(null, "Feedback taken successfully");
    }

    //dasboard
    public function getDashboardDetails()
    {
        $currentUserId = userAuthId();

        $orders =  Rfq::with('buyer')
            ->where('seller_id', $currentUserId)
            ->get();

        $orderStats =  Rfq::with('buyer')
            ->where([
                'seller_id' => $currentUserId,
                'created_at' => Carbon::today()->subDays(7)
            ])->where('status', 'confirmed')->sum('total_amount');

        $rfqs =  Rfq::with('buyer')->where('seller_id', $currentUserId)->get();
        $payouts =  Payout::where('seller_id', $currentUserId)->get();
        $wallet =  UserWallet::where('seller_id', $currentUserId)->first();

        if (!$wallet) {
            UserWallet::create([
                'seller_id' => $currentUserId
            ]);
        }

        DB::table('rfqs')
            ->select('buyer_id', DB::raw('COUNT(*) as total_orders'))
            ->groupBy('id')
            ->where('seller_id', $currentUserId)
            ->count();

        $data = [
            'total_sales' => $orderStats,
            'rfq_recieved' => $rfqs->count(),
            'partners' => $rfqs->count(),
            'rfq_processed' => $rfqs->where('status', '!=', 'pending')->count(),
            'deals_in_progress' => $orders->where('status', 'in-progress')->count(),
            'deals_in_completed' => $orders->where('status', 'confirmed')->count(),
            'withdrawable_balance' => $wallet ? $wallet->master_wallet : 0,
            'pending_withdrawals' => $payouts->where('status', 'pending')->count(),
            'rejected_withdrawals' => $payouts->where('status', 'cancelled')->count(),
            'delivery_charges' => $payouts->where('status', 'paid')->sum('fee'),
            'life_time' => $payouts->where('status', 'paid')->sum('amount'),
            'recent_orders' => $orders,
        ];

        return $this->success($data, "Dashboard details");
    }

    //Withdrawal
    public function getWithdrawalHistory()
    {
        $currentUserId = userAuthId();
        $wallet = UserWallet::where('seller_id', $currentUserId)->first();

        if (!$wallet) {
            UserWallet::create([
                'seller_id' => $currentUserId
            ]);
        }

        $payouts =  Payout::select('amount', 'status', 'created_at')->where('seller_id', $currentUserId)->get();
        return $this->success($payouts, "payouts details");
    }

    public function withdrawalRequest($data)
    {
        $currentUserId = userAuthId();

        $wallet = UserWallet::where('seller_id', $currentUserId)->first();
        if (!$wallet) {
            return $this->error(null, 'User wallet not found', 404);
        }

        $config = Configuration::first();
        if (!$config) {
            return $this->error(null, 'Configuration not found', 500);
        }

        $min = $config->withdrawal_min ?? 0;
        $max = $config->withdrawal_max ?? $wallet->master_wallet;
        $withdrawal_fee = $config->withdrawal_fee ?? 0;

        if (strtolower($config->withdrawal_status) === WithdrawalStatus::DISABLED) {
            return $this->error(null, 'Withrawal on this system is currently not available', 422);
        }

        if ($data->amount > $wallet->master_wallet) {
            return $this->error(null, 'Insufficient balance', 422);
        }

        if ($data->amount < $min) {
            return $this->error(null, 'Minimum withdrawable amount is ' . number_format($min), 422);
        }

        if ($data->amount > $max) {
            return $this->error(null, 'Maximum withdrawable amount is ' . number_format($max), 422);
        }

        $paymentInfo = B2bWithdrawalMethod::where('status', WithdrawalStatus::ACTIVE)->find($data->account_id);
        if (!$paymentInfo) {
            return $this->error(null, 'Invalid account selected for withdrawal', 422);
        }

        $pendingRequest = Payout::where(['seller_id' => $currentUserId, 'status' => 'pending'])->exists();

        if ($pendingRequest) {
            return $this->error(null, 'You have a pending payout request. Please wait for approval.', 422);
        }

        $fee = ($withdrawal_fee / 100) * $data->amount;
        $netAmount = $data->amount - $fee;

        DB::beginTransaction();

        try {

            Payout::create([
                'seller_id' => $currentUserId,
                'amount' => $netAmount,
                'fee' => $fee,
                'b2b_withdrawal_method' => $paymentInfo->id,
            ]);

            $wallet->master_wallet -= $data->amount;
            $wallet->save();

            DB::commit();

            return $this->success('Payout request submitted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(null, 'An error occurred while processing your request', 500);
        }
    }

    //Withdrawal method
    public function addNewMethod($data)
    {
        B2bWithdrawalMethod::create([
            'country_id' => $data->country_id,
            'user_id' => Auth::id(),
            'account_name' => $data->account_name,
            'account_number' => $data->account_number,
            'account_type' => $data->account_type,
            'bank_name' => $data->bank_name,
            'routing_number' => $data->routing_number,
            'bic_swift_code' => $data->bic_swift_code,
        ]);

        return $this->success(null, 'Method added successfully', 201);
    }

    public function getAllMethod()
    {
        $userId = userAuthId();

        if (!$userId) {
            return $this->error(null, 'Unauthorized', 401);
        }

        $user = User::with('B2bWithdrawalMethod')->findOrFail($userId);

        if ($user->B2bWithdrawalMethod->isEmpty()) {
            return $this->error(null, 'No record found', 404);
        }

        $methods = $user->B2bWithdrawalMethod;

        return $this->success($methods, 'All Withdrawal methods', 200);
    }

    public function getSingleMethod($id)
    {
        $method = B2bWithdrawalMethod::select([
            'account_name',
            'account_number',
            'account_type',
            'bank_name',
            'routing_number',
            'bic_swift_code',
            'country_id'
        ])
            ->with(['country:id,name'])
            ->find($id);

        if (!$method) {
            return $this->error(null, 'No record found', 404);
        }

        return $this->success($method, 'Withdrawal details', 200);
    }
    public function updateMethod($id, $data)
    {
        $method = B2bWithdrawalMethod::find($id);

        if (!$method) {
            return $this->error(null, 'No record found', 404);
        }

        $method->update([
            'country_id' => $data->country_id,
            'user_id' => Auth::id(),
            'account_name' => $data->account_name,
            'account_number' => $data->account_number,
            'account_type' => $data->account_type,
            'bank_name' => $data->bank_name,
            'routing_number' => $data->routing_number,
            'bic_swift_code' => $data->bic_swift_code,
        ]);

        return $this->success($method, 'Withdrawal details Updated', 200);
    }

    public function deleteMethod($id)
    {
        $method = B2bWithdrawalMethod::find($id);

        if (!$method) {
            return $this->error(null, 'No record found', 404);
        }

        if ($method->delete()) {
            return $this->success(null, 'Withdrawal details deleted successfully', 200);
        }
        return $this->error(null, 'Failed to delete the record', 500);
    }
}
