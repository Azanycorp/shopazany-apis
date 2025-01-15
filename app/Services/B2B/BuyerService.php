<?php

namespace App\Services\B2B;

use App\Models\Rfq;
use App\Models\User;
use App\Enum\UserType;
use App\Enum\RfqStatus;
use App\Models\Payment;
use App\Enum\UserStatus;
use App\Models\B2bQuote;
use App\Models\B2BProduct;
use App\Enum\ProductStatus;
use App\Models\B2bWishList;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\B2bProdctReview;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\BuyerResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\B2BProductResource;

class BuyerService
{
    use HttpResponse;

    //Admin section
    public function allCustomers()
    {
        $query = trim(request()->input('search'));

        $users = User::where('type', UserType::B2B_BUYER)
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('first_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('middlename', 'LIKE', '%' . $query . '%')
                    ->orWhere('email', 'LIKE', '%' . $query . '%');
            })
            ->paginate(25);

        $data = CustomerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'All Buyers',
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

    public function viewCustomer($id)
    {
        $user = User::with([
            'userCountry',
            'state',
            'wishlist.product',
            'payments.order'
        ])
            ->where('type', UserType::B2B_BUYER)
            ->find($id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $data = new CustomerResource($user);

        return [
            'status' => 'true',
            'message' => 'Buyer details',
            'data' => $data,
        ];
    }

    public function banCustomer($request)
    {
        $user = User::where('type', UserType::B2B_BUYER)
            ->find($request->user_id);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeCustomer($request)
    {
        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            $user->update([
                'status' => UserStatus::DELETED,
                'is_verified' => 0,
                'is_admin_approve' => 0
            ]);

            $user->delete();
        }

        return $this->success(null, "User(s) have been removed successfully");
    }

    public function filter()
    {
        $query = trim(request()->query('approved'));

        $users = User::where('type', UserType::CUSTOMER)
            ->when($query !== null, function ($queryBuilder) use ($query) {
                $queryBuilder->where('is_admin_approve', $query);
            })
            ->paginate(25);

        $data = CustomerResource::collection($users);

        return [
            'status' => 'true',
            'message' => 'Filter by approval',
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

    public function addCustomer($request)
    {
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'middlename' => $request->middlename,
                'email' => $request->email,
                'password' => bcrypt('12345678'),
                'phone' => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'type' => UserType::CUSTOMER,
                'is_verified' => 1,
                'is_admin_approve' => 1,
                'status' => $request->status,
            ]);

            $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : null;

            $user->update(['image' => $image]);

            return $this->success(null, "User has been created successfully", 201);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function editCustomer($request)
    {
        $user = User::where('type', 'customer')
            ->find($request->user_id);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }

        $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'image' => $image,
            'status' => $request->status,
        ]);

        return $this->success(null, "User has been updated successfully");
    }

    public function getPayment($id)
    {
        $payment = Payment::with(['user', 'order'])->findOrFail($id);
        $data = new PaymentResource($payment);

        return $this->success($data, "Payment detail");
    }


    public function requestRefund($request)
    {
        $complaintNumber = generateRefundComplaintNumber();

        B2BRequestRefund::create([
            'user_id' => $request->user_id,
            'b2b_product_id' => $request->b2b_product_id,
            'complaint_number' => $complaintNumber,
            'order_number' => $request->order_number,
            'type' => $request->type,
            'additional_note' => $request->additional_note,
            'send_reply' => $request->send_reply,
            'status' => RefundRequestStatus::PENDING,
        ]);

        return $this->success(null, 'Request sent successful', 201);
    }

    public function getProducts()
    {
        $products = B2BProduct::with(['category', 'subCategory', 'country', 'b2bProductImages'])
            ->where('status', ProductStatus::ACTIVE)
            ->get();

        $data = B2BProductResource::collection($products);

        return $this->success($data, 'Products');
    }


    public function getProductDetail($slug)
    {
        $product = B2BProduct::with(['category','user', 'country', 'b2bProductImages','b2bProdctReview'])
            ->where('slug', $slug)
            ->firstOrFail();

        $moreFromSeller = B2BProduct::with(['category','user','subCategory', 'country', 'b2bProductImages','b2bProdctReview'])
            ->where('user_id', $product->user_id)->get();

        $relatedProducts = B2BProduct::with(['category','user','subCategory', 'country', 'b2bProductImages','b2bProdctReview'])
            ->where('category_id', $product->category_id)->get();

         $data = new B2BProductResource($product);

        $response = [
            'data' => $data,
            'more_from_seller' => B2BProductResource::collection($moreFromSeller),
            'related_products' => B2BProductResource::collection($relatedProducts),
        ];

        return $this->success($response, 'Product Details');
    }

    //Quotes
    public function allQuotes()
    {
        $userId = userAuthId();

        $quotes = B2bQuote::where('buyer_id', $userId)
            ->latest('id')
            ->get();

        if ($quotes->isEmpty()) {
            return $this->error(null, 'No record found to send', 404);
        }

        return $this->success($quotes, 'quotes lists');
    }

    public function sendMutipleQuotes()
    {
        $userId = userAuthId();

        $quotes = B2bQuote::where('buyer_id', $userId)->latest('id')->get();

        if ($quotes->isEmpty()) {
            return $this->error(null, 'No record found to send', 404);
        }

        DB::beginTransaction();

        try {
            $rfqs = [];

            foreach ($quotes as $quote) {

                if (empty($quote->product_data['unit_price']) || empty($quote->product_data['minimum_order_quantity'])) {
                    throw new \Exception('Invalid product data');
                }

                $rfqs[] = [
                    'buyer_id' => $quote->buyer_id,
                    'seller_id' => $quote->seller_id,
                    'quote_no' => strtoupper(Str::random(10) . $userId),
                    'product_id' => $quote->product_id,
                    'product_quantity' => $quote->qty,
                    'total_amount' => $quote->product_data['unit_price'] * $quote->product_data['minimum_order_quantity'],
                    'p_unit_price' => $quote->product_data['unit_price'],
                    'product_data' => $quote->product_data,
                ];
            }

            Rfq::insert($rfqs);
            B2bQuote::where('buyer_id', $userId)->delete();

            DB::commit();
            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }

    public function sendRfq($id)
    {
        $quote = B2bQuote::find($id);

        if (!$quote) {
            return $this->error(null, 'No record found', 404);
        }

        try {
            $amount = ($quote->product_data['unit_price'] * $quote->product_data['minimum_order_quantity']);

            Rfq::create([
                'buyer_id' => $quote->buyer_id,
                'seller_id' => $quote->seller_id,
                'quote_no' => strtoupper(Str::random(10) . Auth::user()->id),
                'product_id' => $quote->product_id,
                'product_quantity' => $quote->qty,
                'total_amount' => $amount,
                'p_unit_price' => $quote->product_data['unit_price'],
                'product_data' => $quote->product_data,
            ]);

            $quote->delete();

            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            return $this->error(null, 'transaction failed, please try again', 500);
        }
    }

    public function removeQuote($id)
    {
        $quote = B2bQuote::find($id);

        if (!$quote) {
            return $this->error(null, 'No record found', 404);
        }

        $quote->delete();
        return $this->success(null, 'Item removed successfully');
    }
    public function sendQuote($data)
    {
        $product = B2BProduct::findOrFail($data->product_id);

        $quote = B2bQuote::where('product_id', $data->product_id)->first();

        if ($quote) {
            return $this->error(null, 'Product already exist');
        }

        if ($data->qty < $product->minimum_order_quantity) {
            return $this->error(null, 'Your peferred quantity can not be less than the one already set', 422);
        }

        $quote = B2bQuote::create([
            'buyer_id' => userAuthId(),
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'product_data' => $product,
            'qty' => $data->qty,
        ]);

        return $this->success($quote, 'quote Added successfully');
    }

    //dasboard
    public function getDashboardDetails()
    {
        $currentUserId = userAuthId();
        $startDate = now()->subDays(7); // 7 days ago
        $endDate = now(); // Current date and time

        $rfqStats = Rfq::select(
            DB::raw('COUNT(*) as total_rfqs'),
            DB::raw('SUM(CASE WHEN status != ? THEN 1 ELSE 0 END) as rfq_accepted', [RfqStatus::PENDING]),
            DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as deals_in_progress', [RfqStatus::IN_PROGRESS]),
            DB::raw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as deals_completed', [RfqStatus::DELIVERED])
        )
            ->where('buyer_id', $currentUserId)
            ->first();

        $orderStats = Rfq::where('buyer_id', $currentUserId)
            ->where('status', RfqStatus::CONFIRMED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $uniqueSellersCount = Rfq::where('buyer_id', $currentUserId)
            ->distinct('seller_id')
            ->count('seller_id');

        $recentOrders = Rfq::with('seller')
            ->where('buyer_id', $currentUserId)
            ->latest()
            ->take(10)
            ->get();

        $data = [
            'total_sales' => $orderStats,
            'partners' => $uniqueSellersCount,
            'rfq_sent' => $rfqStats->total_rfqs ?? 0,
            'rfq_accepted' => $rfqStats->rfq_accepted ?? 0,
            'deals_in_progress' => $rfqStats->deals_in_progress ?? 0,
            'deals_completed' => $rfqStats->deals_completed ?? 0,
            'recent_orders' => $recentOrders,
        ];

        return $this->success($data, "Dashboard details");
    }


    public function allRfqs()
    {
        $userId = userAuthId();

        $rfqs = Rfq::with('seller')->where('buyer_id', $userId)
            ->latest('id')
            ->get();

        if ($rfqs->isEmpty()) {
            return $this->error(null, 'No record found to send', 404);
        }

        return $this->success($rfqs, 'rfqs lists');
    }

    public function rfqDetails($id)
    {
        $rfq = Rfq::with(['seller', 'messages'])->find($id);

        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }

        return $this->success($rfq, 'rfq details');
    }

    //send review request to vendor
    public function sendReviewRequest($data)
    {
        $rfq = Rfq::find($data->rfq_id);

        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }

        DB::beginTransaction();

        try {

            $rfq->messages()->create([
                'rfq_id' => $data->rfq_id,
                'p_unit_price' => $data->p_unit_price,
                'preferred_qty' => $rfq->qty,
                'note' => $data->note,
            ]);

            $rfq->update(['status' => 'review']);

            DB::commit();

            return $this->success($rfq, 'Review sent successfully with details.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'Failed to send review request: ' . $e->getMessage(), 500);
        }
    }

    //send review request to vendor
    public function acceptQuote($data)
    {
        $rfq = Rfq::find($data->rfq_id);

        if (!$rfq) {
            return $this->error(null, 'No record found to send', 404);
        }

        $rfq->update([
            'status' => 'in-progress',
        ]);

        return $this->success($rfq, 'Quote Accepted successfully');
    }

    //send review request to vendor
    public function addPreview($data)
    {
        $review = B2bProdctReview::where(['buyer_id' => Auth::id(), 'product_id' => $data->product_id])->first();
        if ($review) {
            $review->update([
                'product_id' => $data->product_id,
                'rating' => $data->rating,
                'title' => $data->title,
                'note' => $data->note,
            ]);
            return $this->success(null, 'Review Updated successfully');
        }
        B2bProdctReview::create([
            'product_id' => $data->product_id,
            'buyer_id' => Auth::id(),
            'rating' => $data->rating,
            'title' => $data->title,
            'note' => $data->note,
        ]);
        return $this->success(null, 'Review Sent successfully');
    }

    public function addToWishList($data)
    {
        $product = B2BProduct::find($data->product_id);

        if (!$product) {
            return $this->error(null, 'No record found to send', 404);
        }

        B2bWishList::create([
            'user_id' => userAuthId(),
            'product_id' => $product->id
        ]);

        return $this->success(null, 'Product Added successfully');
    }

    //wish list
    public function myWishList()
    {
        $wishes =  B2bWishList::with('product')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        if ($wishes->isEmpty()) {
            return $this->error(null, 'No record found to send', 404);
        }

        return $this->success($wishes, 'My Wish List');
    }

    public function removeItem($id)
    {
        $wish =  B2bWishList::find($id);

        if (!$wish) {
            return $this->error(null, 'No record found to send', 404);
        }

        $wish->delete();
        return $this->success(null, 'Item Removed');
    }

    //Account section

    public function profile()
    {
        $auth = userAuth();
        $user = User::with('b2bCompany')
            ->where('type', UserType::B2B_BUYER)
            ->find($auth->id);

        if (!$user) {
            return $this->error(null, 'User does not exist');
        }

        $data = new BuyerResource($user);

        return $this->success($data, 'Buyer profile');
    }

    public function editAccount($request)
    {
        $auth = Auth::user();

        $user = User::findOrFail($auth->id);

        $image = $request->hasFile('logo') ? uploadUserImage($request, 'logo', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone,
            'image' => $image
        ]);

        return $this->success(null, "Profile Updated successfully");
    }

    public function changePassword($request)
    {
        $user = $request->user();

        if (Hash::check($request->old_password, $user->password)) {
            $user->update([
                'password' => bcrypt($request->new_password),
            ]);

            return $this->success(null, 'Password Successfully Updated');
        } else {
            return $this->error(null, 422, 'Old Password did not match');
        }
    }

    public function editCompany($request)
    {
        $auth = Auth::user();
        $user = User::with('b2bCompany')->findOrFail($auth->id);

        $user->b2bCompany()->update([
            'company_name' => $request->company_name ?? $user->company_name,
            'company_size' => $request->company_size ?? $user->company_size,
            'website' => $request->website ?? $user->website,
            'average_spend' => $request->average_spend ?? $user->average_spend,
            'service_type' => $request->service_type ?? $user->service_type,
            'country_id' => $request->country_id ?? $user->country,
        ]);

        return $this->success(null, "Details Updated successfully");
    }
}
