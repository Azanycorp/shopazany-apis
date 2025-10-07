<?php

namespace App\Services\B2B;

use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\ProductStatus;
use App\Enum\TransactionStatus;
use App\Enum\UserType;
use App\Enum\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\B2BOrderResource;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\B2BSellerProfileResource;
use App\Http\Resources\B2BSellerShippingAddressResource;
use App\Imports\B2BProductImport;
use App\Imports\ProductImport;
use App\Mail\B2BDeliveredOrderMail;
use App\Mail\B2BOrderEmail;
use App\Mail\B2BSHippedOrderMail;
use App\Models\B2bOrder;
use App\Models\B2bOrderFeedback;
use App\Models\B2bOrderRating;
use App\Models\B2BProduct;
use App\Models\B2BSellerShippingAddress;
use App\Models\PaymentMethod;
use App\Models\Rfq;
use App\Models\RfqMessage;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\WithdrawalRequest;
use App\Repositories\B2BProductRepository;
use App\Repositories\B2BSellerShippingRepository;
use App\Services\TransactionService;
use App\Trait\HttpResponse;
use App\Trait\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SellerService extends Controller
{
    use HttpResponse, Payment;

    public function __construct(
        protected B2BProductRepository $b2bProductRepository,
        protected B2BSellerShippingRepository $b2bSellerShippingRepository
    ) {}

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

        $businessDoc = $request->hasFile('business_reg_document') ?
            uploadImage($request, 'business_reg_document', $folder) :
            ['url' => null];

        $identifyTypeDoc = ['url' => null];

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
            'business_reg_document' => $businessDoc['url'],
            'identification_type' => $request->identification_type,
            'identification_type_document' => $identifyTypeDoc['url'],
            'agree' => $request->agree,
        ]);

        return $this->success(null, 'Created successfully');
    }

    public function profile()
    {
        $authId = userAuthId();

        $user = User::findOrFail($authId);

        return $this->success(new B2BSellerProfileResource($user), 'Seller profile');
    }

    public function editAccount($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::find($currentUserId);

        $image = $request->hasFile('logo') ?
            uploadUserImage($request, 'logo', $user) :
            ['url' => $user->image, 'public_id' => $user->public_id];

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'middlename' => $request->middlename ?? $user->middlename,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'image' => $image['url'],
            'public_id' => $image['public_id'],
        ]);

        return $this->success(null, 'Updated successfully');
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
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('businessInformation')->findOrFail($request->user_id);

        if ($request->hasFile('logo')) {
            $url = uploadFunction($request->file('logo'), 'logo');
            $image = $request->hasFile('logo') ? $url['url'] : ['url' => $user->businessInformation->logo];
        }

        $user->businessInformation()->update([
            'business_name' => $request->business_name,
            'business_phone' => $request->business_phone,
            'country_id' => $request->country_id,
            'city' => $request->city,
            'zip' => $request->postal_code,
            'address' => $request->address,
            'state' => $request->state,
            'logo' => $image['url'] ?? null,
        ]);

        return $this->success(null, 'Updated successfully');
    }

    public function exportSellerProduct($request, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        switch ($request->type) {
            case 'product':
                return $this->exportB2bProduct($userId, $request);

            case 'order':
                return 'None yet';

            default:
                return 'Type not found';
        }
    }

    public function b2bproductImport($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $seller = User::find($currentUserId);

        if (! $seller) {
            return $this->error(null, 'User details not found.', 404);
        }
        try {
            Excel::import(new B2BProductImport($seller), $request->file('file'));

            return $this->success(null, 'Imported successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function addProduct($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::find($currentUserId);

        if (! $user) {
            return $this->error(null, 'User details not found.', 404);
        }

        try {

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
                'user_id' => $request->user_id,
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
                'type' => $request->type ?? null,
                'availability_quantity' => $request->quantity,
                'default_currency' => $user->default_currency,
                'fob_price' => $request->fob_price,
                'status' => ProductStatus::ACTIVE,
                'country_id' => is_int($user->country) ? $user->country : 160,
            ];

            $product = $this->b2bProductRepository->create($data);

            if ($request->hasFile('images')) {
                $folder = folderNames('product', $name, null, 'images');
                uploadMultipleB2BProductImage($request, 'images', $folder->folder, $product);
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
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::select('id')->findOrFail($request->user_id)->id;

        $search = request()->input('search');

        $products = $this->b2bProductRepository->all($user, $search);

        $data = B2BProductResource::collection($products);

        return $this->success($data, 'All products');
    }

    public function getProductById(int $product_id, $user_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $prod = $this->b2bProductRepository->find($product_id);

        return $this->success(new B2BProductResource($prod), 'Product details');
    }

    public function updateProduct($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::findOrFail($request->user_id);

        $prod = B2BProduct::find($request->product_id);

        if (! $prod) {
            return $this->error(null, 'No product found with this Id.', 404);
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
            'user_id' => $request->user_id,
            'name' => $request->name ?? $prod->name,
            'slug' => $slug,
            'category_id' => $request->category_id ?? $prod->category_id,
            'sub_category_id' => $request->sub_category_id ?? $prod->sub_category_id,
            'keywords' => $request->keywords ?? $prod->keywords,
            'description' => $request->description ?? $prod->description,
            'front_image' => $url['url'] ?? $prod->front_image,
            'public_id' => $url['public_id'] ?? $prod->public_id,
            'minimum_order_quantity' => $request->minimum_order_quantity ?? $prod->minimum_order_quantity,
            'unit_price' => $request->unit ?? $prod->unit_price,
            'quantity' => $request->quantity ?? $prod->quantity,
            'default_currency' => $request->default_currency ?? $prod->default_currency,
            'availability_quantity' => $request->quantity - $prod->sold,
            'fob_price' => $request->fob_price ?? $prod->fob_price ?? 0,
            'country_id' => $user->country ?? 160,
        ];

        $product = $this->b2bProductRepository->update($request->product_id, $data);

        if ($request->hasFile('images')) {
            $product->b2bProductImages()->delete();
            $folder = folderNames('product', $name, null, 'images');
            uploadMultipleB2BProductImage($request, 'images', $folder->folder, $product);
        }

        return $this->success(null, 'Product updated successfully');
    }

    public function deleteProduct(int $user_id, $product_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $prod = B2BProduct::where('user_id', $user_id)
            ->where('id', $product_id)
            ->firstOrFail();

        $this->b2bProductRepository->delete($prod->id);

        return $this->success(null, 'Deleted successfully');
    }

    public function getAnalytics($user_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with(['b2bProducts.category'])
            ->withCount(['b2bProducts', 'b2bProducts as category_count' => function ($query): void {
                $query->distinct('category_id');
            }])
            ->findOrFail($user_id);

        $data = [
            'product_count' => $user->b2b_products_count,
            'category_count' => $user->category_count,
        ];

        return $this->success($data, 'Analytics');
    }

    public function addShipping($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
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
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $address = $this->b2bSellerShippingRepository->all($user_id);
        $data = B2BSellerShippingAddressResource::collection($address);

        return $this->success($data, 'All address');
    }

    public function getShippingById($user_id, int $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $shipping = $this->b2bSellerShippingRepository->find($shipping_id);

        return $this->success(new B2BSellerShippingAddressResource($shipping), 'Address detail');
    }

    public function updateShipping($request, int $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
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

    public function deleteShipping($user_id, int $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $this->b2bSellerShippingRepository->delete($shipping_id);

        return $this->success(null, 'Deleted successfully');
    }

    public function setDefault($user_id, $shipping_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $shipping = B2BSellerShippingAddress::where('user_id', $currentUserId)
            ->where('id', $shipping_id)
            ->firstOrFail();

        if (! $shipping) {
            return $this->error(null, 'No record found', 404);
        }

        B2BSellerShippingAddress::where('user_id', $currentUserId)
            ->where('is_default', 1)
            ->update(['is_default' => 0]);

        $shipping->update([
            'is_default' => 1,
        ]);

        return $this->success(null, 'Address Set as default successfully');
    }

    public function getComplaints($user_id)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

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

        return $this->success($data, 'Product template');
    }

    public function productImport($request)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $seller = userAuth();

        try {
            Excel::import(new ProductImport($seller), $request->file('file'));

            return $this->success(null, 'Imported successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function export($userId, $data)
    {
        $currentUserId = userAuthId();

        if ($currentUserId != $userId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        switch ($data->type) {
            case 'product':
                return $this->b2bExportProduct($userId);

            case 'order':
                return 'None yet';

            default:
                return 'Type not found';
        }
    }

    public function getEarningReport()
    {
        $currentUserId = userAuthId();
        if (! $currentUserId) {
            return $this->error(null, 'Unauthorized action.', 401);
        }
        $startDate = now()->subDays(30);

        $endDate = now();

        $currentUserId = userAuthId();
        $totalSales = B2bOrder::where('seller_id', $currentUserId)
            ->where('status', OrderStatus::DELIVERED)
            ->sum('total_amount');

        $payouts = WithdrawalRequest::where('user_id', $currentUserId)->get();
        // payouts this month
        $monthlyPayout = WithdrawalRequest::where([
            'user_id' => $currentUserId,
            'status' => WithdrawalStatus::COMPLETED,
        ])->whereBetween('created_at', [$startDate, $endDate])->sum('amount');
        // order this month
        $monthlyOrder = Rfq::where([
            'seller_id' => $currentUserId,
            'status' => OrderStatus::DELIVERED,
        ])->whereBetween('created_at', [$startDate, $endDate])->sum('total_amount');

        $data = (object) [
            'total_sales_alltime' => $totalSales,
            'sales_this_month' => $monthlyOrder,
            'total_payout' => $payouts->where('status', OrderStatus::PAID)->sum('amount'),
            'payout_this_month' => $monthlyPayout,
        ];

        return $this->success($data, 'Earning details');
    }

    public function getOrderDetails($id)
    {
        $order = B2bOrder::with(['buyer', 'seller'])->where('seller_id', userAuthId())
            ->where('id', $id)
            ->firstOrFail();

        return $this->success(new B2BOrderResource($order), 'order details');
    }

    // Rfq
    public function getAllRfq()
    {
        $searchQuery = request()->input('search');
        $orders_count = B2bOrder::where('seller_id', userAuthId())->count();

        $rfqs = Rfq::with('buyer')
            ->where('seller_id', userAuthId())
            ->latest()
            ->get();

        $orders = B2bOrder::where('seller_id', userAuthId())->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
            $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                $subQuery->where('seller_id', userAuthId())
                    ->where('order_no', 'LIKE', '%'.$searchQuery.'%');
            });
        })->latest()->get();

        $data = [
            'total_rfqs' => $rfqs->count(),
            'rfqs' => $rfqs,
            'total_orders' => $orders_count,
            'orders' => $orders,
        ];

        return $this->success($data, 'orders');
    }

    public function getRfqDetails($id)
    {
        $rfq = Rfq::with(['buyer', 'seller'])->findOrFail($id);

        $messages = RfqMessage::with(['seller', 'buyer'])->where('rfq_id', $rfq->id)->get();

        $data = [
            'rfq' => $rfq,
            'messages' => $messages,
        ];

        return $this->success($data, 'rfq details');
    }

    public function markShipped($data)
    {
        $order = B2bOrder::where('seller_id', userAuthId())->find($data->order_id);

        if (! $order) {
            return $this->error(null, 'order not found');
        }

        $user = User::find($order->buyer_id);

        if (! $user) {
            return $this->error(null, 'Buyer not found');
        }

        $order->update([
            'status' => OrderStatus::SHIPPED,
            'shipped_date' => now()->toDateString(),
        ]);

        $orderedItems = [
            'quantity' => $order->product_quantity,
            'price' => $order->total_amount,
            'buyer_name' => $user->first_name.' '.$user->last_name,
            'order_number' => $order->order_no,
        ];

        $type = MailingEnum::ORDER_EMAIL;
        $subject = 'B2B Order Shipped Confirmation';
        $mail_class = B2BSHippedOrderMail::class;
        mailSend($type, $user, $subject, $mail_class, $orderedItems);

        return $this->success($order, 'order Shipped successfully');
    }

    public function replyRequest($request)
    {
        $rfq = Rfq::find($request->rfq_id);

        if (! $rfq) {
            return $this->error(null, 'No record found details', 404);
        }

        $amount = ($request->preferred_unit_price * $rfq->product_quantity);

        $message = RfqMessage::create([
            'rfq_id' => $rfq->id,
            'seller_id' => userAuthId(),
            'p_unit_price' => $request->preferred_unit_price,
            'note' => $request->note,
        ]);

        $rfq->update([
            'p_unit_price' => $request->preferred_unit_price,
            'total_amount' => $amount,
        ]);

        return $this->success($message, 'message details');
    }

    public function markDelivered($data)
    {
        $order = B2bOrder::where('seller_id', userAuthId())->findOrFail($data->order_id);

        $user = User::find($order->buyer_id);

        if (! $user) {
            return $this->error(null, 'Buyer details not found', 404);
        }

        $order->update([
            'status' => OrderStatus::DELIVERED,
            'delivery_date' => now()->toDateString(),
        ]);

        $orderedItems = [
            'quantity' => $order->product_quantity,
            'buyer_name' => $user->buyerName,
            'order_number' => $order->order_no,
        ];

        $type = MailingEnum::ORDER_EMAIL;
        $subject = 'B2B Order Delivery Confirmation';
        $mail_class = B2BDeliveredOrderMail::class;
        mailSend($type, $user, $subject, $mail_class, $orderedItems);

        return $this->success($order, 'Order marked delivered successfully');
    }

    public function confirmPayment($request)
    {
        $rfq = Rfq::findOrFail($request->rfq_id);

        $seller = User::select('id')->findOrFail($rfq->seller_id);

        $buyer = User::select('id', 'email', 'first_name', 'last_name')->findOrFail($rfq->buyer_id);

        $product = B2BProduct::select(['id', 'name', 'front_image', 'quantity', 'sold'])->findOrFail($rfq->product_id);

        DB::beginTransaction();

        try {
            $amount = $rfq->total_amount;

            $total_amount = currencyConvert(
                userAuth()->default_currency,
                $amount,
                $product->shopCountry->currency ?? 'USD',
            );

            $order = B2bOrder::create([
                'buyer_id' => $rfq->buyer_id,
                'seller_id' => $rfq->seller_id,
                'product_id' => $rfq->product_id,
                'product_quantity' => $rfq->product_quantity,
                'order_no' => 'ORD-'.now()->timestamp.'-'.Str::random(8),
                'product_data' => $product,
                'total_amount' => $total_amount,
                'payment_method' => PaymentType::OFFLINE,
                'payment_status' => OrderStatus::PAID,
                'status' => OrderStatus::PENDING,
            ]);

            $orderedItems = [
                'product_name' => $product->name,
                'image' => $product->front_image,
                'quantity' => $rfq->product_quantity,
                'price' => $total_amount,
                'buyer_name' => $buyer->first_name.' '.$buyer->last_name,
                'order_number' => $order->order_no,
                'currency' => $buyer->default_currency ?? userAuth()->default_currency,
            ];

            $orderItemData = [
                'orderedItems' => $orderedItems,
            ];

            $product->quantity -= $rfq->product_quantity;
            $product->sold += $rfq->product_quantity;
            $product->save();

            $wallet = UserWallet::firstOrCreate(
                ['seller_id' => $seller->id],
                ['master_wallet' => 0]
            );

            $wallet->increment('master_wallet', $total_amount);

            $rfq->update([
                'payment_status' => OrderStatus::PAID,
                'status' => OrderStatus::COMPLETED,
            ]);

            DB::commit();

            $type = MailingEnum::ORDER_EMAIL;
            $subject = 'B2B Order Confirmation';
            $mail_class = B2BOrderEmail::class;
            mailSend($type, $buyer, $subject, $mail_class, $orderItemData);

            return $this->success($order, 'Payment Confirmed successfully');
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function cancelOrder($request)
    {
        $order = B2bOrder::where('seller_id', userAuthId())->find($request->order_id);

        if (! $order) {
            return $this->error(null, 'Order not found', 404);
        }

        $order->update([
            'status' => OrderStatus::CANCELLED,
        ]);

        $product = B2BProduct::find($order->product_id);

        if (! $order) {
            return $this->error(null, 'Product not found', 404);
        }

        $product->availability_quantity += $order->product_quantity;
        $product->sold -= $order->product_quantity;
        $product->save();

        return $this->success(null, 'Order Cancelled successful');
    }

    public function rateOrder($request)
    {
        $userId = userAuthId();

        $order = B2bOrder::where('seller_id', $userId)
            ->where('id', $request->order_id)
            ->firstOrFail();

        B2bOrderRating::create([
            'seller_id' => $userId,
            'order_no' => $order->order_no,
            'rating' => $request->rating,
            'description' => $request->description ? $request->description : 'description',
        ]);

        return $this->success(null, 'Rating successful');
    }

    public function orderFeeback($request)
    {
        $userId = userAuthId();
        $order = B2bOrder::where('seller_id', $userId)
            ->where('id', $request->order_id)
            ->firstOrFail();

        B2bOrderFeedback::create([
            'seller_id' => $userId,
            'order_no' => $order->order_no,
            'description' => $request->description,
        ]);

        return $this->success(null, 'Feedback taken successfully');
    }

    // dasboard
    public function getDashboardDetails()
    {
        $currentUserId = userAuthId();
        $orders = B2bOrder::with('buyer')
            ->where('seller_id', $currentUserId)
            ->get();

        $uniqueSellersCount = B2bOrder::where(['seller_id' => $currentUserId, 'status' => OrderStatus::DELIVERED])
            ->distinct('buyer_id')
            ->count('buyer_id');

        $seven_days_partners = B2bOrder::where(['seller_id' => $currentUserId, 'status' => OrderStatus::DELIVERED])
            ->distinct('buyer_id')
            ->where('created_at', '<=', Carbon::today()->subDays(7))
            ->count('buyer_id');

        $orderStats = B2bOrder::where([
            'seller_id' => $currentUserId,
        ])->where('status', OrderStatus::DELIVERED)->sum('total_amount');

        $seven_days_orderStats = B2bOrder::where([
            'seller_id' => $currentUserId,
            'status' => OrderStatus::DELIVERED,
        ])->where('created_at', '<=', Carbon::today()->subDays(7))->sum('total_amount');

        $rfqs = Rfq::with('buyer')->where('seller_id', $currentUserId)->get();

        $payouts = WithdrawalRequest::where('user_id', $currentUserId)->get();

        $wallet = UserWallet::firstOrCreate(
            ['seller_id' => $currentUserId]
        );

        $data = [
            'total_sales' => $orderStats,
            'seven_days_sales' => $seven_days_orderStats,
            'rfq_recieved' => $rfqs->count(),
            'partners' => $uniqueSellersCount,
            'seven_days_partners' => $seven_days_partners,
            'rfq_processed' => $rfqs->where('status', OrderStatus::COMPLETED)->count(),
            'deals_in_progress' => $orders->where('status', OrderStatus::PAID)->count(),
            'deals_in_completed' => $orders->where('status', OrderStatus::DELIVERED)->count(),
            'withdrawable_balance' => $wallet ? $wallet->master_wallet : 0,
            'pending_withdrawals' => $payouts->where('status', WithdrawalStatus::PENDING)->count(),
            'rejected_withdrawals' => $payouts->where('status', WithdrawalStatus::FAILED)->count(),
            'delivery_charges' => $payouts->where('status', OrderStatus::PAID)->sum('fee'),
            'life_time' => $payouts->where('status', OrderStatus::PAID)->sum('amount'),
            'recent_orders' => $orders,
        ];

        return $this->success($data, 'Dashboard details');
    }

    // Withdrawal
    public function getWithdrawalHistory()
    {
        $currentUserId = userAuthId();

        $wallet = UserWallet::firstOrCreate(
            ['seller_id' => $currentUserId]
        );

        $payouts = WithdrawalRequest::where('user_id', $currentUserId)->get();

        return $this->success($payouts, 'payouts details');
    }

    public function withdrawalRequest($request)
    {
        $currentUserId = userAuthId();

        $user = User::with('paymentMethods')
            ->where('id', $currentUserId)
            ->first();

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $wallet = UserWallet::firstOrCreate(
            ['seller_id' => $currentUserId]
        );

        if ($request->amount > $wallet->master_wallet) {
            return $this->error(null, 'Insufficient balance', 422);
        }

        if ($request->amount < 500) {
            return $this->error(null, 'Minimum withdrawable amount is 500', 422);
        }

        $paymentInfo = PaymentMethod::where('is_default', true)->find($request->account_id);

        if (! $paymentInfo) {
            return $this->error(null, 'account selected for withdrawal not found', 404);
        }

        $newBalance = $wallet->master_wallet - $request->amount;

        DB::beginTransaction();

        try {

            WithdrawalRequest::create([
                'user_id' => $user->id,
                'user_type' => $user->type,
                'amount' => $request->amount,
                'previous_balance' => $wallet->master_wallet,
                'current_balance' => $newBalance,
                'status' => WithdrawalStatus::PENDING,
            ]);

            $wallet->update(['master_wallet' => $newBalance]);
            (new TransactionService($user, TransactionStatus::WITHDRAWAL, $request->amount))->logTransaction();
            DB::commit();

            return $this->success('Payout request submitted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(null, 'An error occurred while processing your request :'.$e->getMessage(), 500);
        }
    }

    // Withdrawal method
    public function addNewMethod($request)
    {
        $auth = Auth::user();

        if (! $auth) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        if ($auth->type === UserType::B2B_BUYER) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        if ($auth->id !== $request->user_id || (! $auth->is_affiliate_member && $auth->type !== UserType::B2B_SELLER)) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('paymentMethods')->find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        if ($user->paymentMethods->count() >= 3) {
            return $this->error(null, 'You can only add up to 3 payment methods', 400);
        }

        switch ($request->type) {
            case 'bank_transfer':
                $methodAdded = $this->addBankTransfer($request, $user);
                break;

            case 'paypal':
                $methodAdded = $this->addPayPal($request, $user);
                break;

            default:
                return $this->error(null, 'Invalid type', 400);
        }

        return $methodAdded;
    }

    public function getAllMethod()
    {
        $userId = userAuthId();

        if (! $userId) {
            return $this->error(null, 'Unauthorized', 401);
        }

        $user = User::with('paymentMethods')->findOrFail($userId);

        if ($user->paymentMethods->isEmpty()) {
            return $this->error(null, 'No record found', 404);
        }

        $methods = $user->paymentMethods;

        return $this->success($methods, 'All Withdrawal methods', 200);
    }

    public function getSingleMethod($id)
    {
        $method = PaymentMethod::select([
            'account_name',
            'account_number',
            'bank_name',
        ])->where('user_id', userAuthId())->where('id', $id)->firstOrFail();

        return $this->success($method, 'Withdrawal details', 200);
    }

    public function updateMethod($request, $id)
    {
        $userId = userAuthId();

        if ($userId !== $request->user_id) {
            return $this->error(null, 'Unauthorized', 401);
        }

        $method = PaymentMethod::where('user_id', userAuthId())->where('id', $id)->firstOrFail();

        $method->update([
            'account_name' => $request->account_name,
            'account_number' => $request->account_number,
            'bank_name' => $request->bank_name,
        ]);

        return $this->success($method, 'Withdrawal details Updated', 200);
    }

    public function makeAccounDefaultt($request)
    {
        $method = PaymentMethod::findOrFail($request->id);

        PaymentMethod::where('user_id', userAuthId())
            ->where('is_default', true)
            ->update(['is_default' => 0]);

        $method->update([
            'is_default' => true,
        ]);

        return $this->success($method, 'Withdrawal details set to default', 200);
    }

    public function deleteMethod($id)
    {
        $method = PaymentMethod::findOrFail($id);

        $method->delete();

        return $this->success(null, 'Withdrawal details deleted successfully', 200);
    }
}
