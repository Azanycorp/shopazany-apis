<?php

namespace App\Services\B2B;

use Carbon\Carbon;
use App\Models\Rfq;
use App\Models\User;
use App\Enum\UserType;
use App\Enum\RfqStatus;
use App\Models\Payment;
use App\Enum\UserStatus;
use App\Models\B2bOrder;
use App\Models\B2bQuote;
use App\Enum\OrderStatus;
use App\Models\B2bBanner;
use App\Models\B2bCompany;
use App\Models\B2BProduct;
use App\Models\HomeBanner;
use App\Models\RfqMessage;
use App\Enum\ProductStatus;
use App\Models\B2bWishList;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\B2bProdctLike;
use App\Models\B2bProdctReview;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use App\Models\B2bProductCategory;
use Illuminate\Support\Facades\DB;
use App\Models\BuyerShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\BuyerResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\B2BOrderResource;
use App\Http\Resources\B2BQuoteResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\B2BBannerResource;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\B2BCategoryResource;
use App\Http\Resources\B2BWishListResource;
use App\Http\Resources\B2BSellerProductResource;
use App\Http\Resources\B2BBestSellingProductResource;
use App\Http\Resources\B2BBuyerShippingAddressResource;

class BuyerService
{
    use HttpResponse;

    //Admin section
    public function allCustomers(): array
    {
        $query = trim(request()->input('search'));

        $users = User::where('type', UserType::B2B_BUYER)
            ->where(function ($queryBuilder) use ($query): void {
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
            ->findOrFail($id);


        $data = new CustomerResource($user);

        return $this->success($data, 'Buyer details');
    }

    public function banCustomer($request)
    {
        $user = User::where('type', UserType::B2B_BUYER)
            ->findOrFail($request->user_id);

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, "User has been blocked successfully");
    }

    public function removeCustomer($request)
    {
        DB::transaction(function () use ($request) {
            User::whereIn('id', $request->user_ids)
                ->update([
                    'status' => UserStatus::DELETED,
                    'is_verified' => 0,
                    'is_admin_approve' => 0
                ]);

            User::whereIn('id', $request->user_ids)->delete();
        });

        return $this->success(null, "User(s) have been removed successfully");
    }

    public function filter(): array
    {
        $query = trim(request()->query('approved'));

        $users = User::where('type', UserType::CUSTOMER)
            ->when($query !== null, function ($queryBuilder) use ($query): void {
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

    public function getBanners()
    {
        $banners = B2bBanner::where('status', ProductStatus::ACTIVE)
            ->get();

        $data = B2BBannerResource::collection($banners);

        return $this->success($data, 'banners');
    }
    public function getHomeBanners()
    {
        $banners = HomeBanner::first();
        return $this->success($banners, 'home-banners');
    }

    public function getProducts()
    {
        $products = B2BProduct::with([
            'category',
            'user',
            'b2bLikes',
            'b2bProductReview',
            'subCategory',
            'country',
            'b2bProductImages'
        ])
            ->where('status', ProductStatus::ACTIVE)
            ->get();

        $data = B2BProductResource::collection($products);

        return $this->success($data, 'Products');
    }
    public function categories()
    {
        $categories = B2bProductCategory::with(['subcategory', 'products', 'products.b2bProductReview', 'products.b2bLikes'])
            ->withCount('products') // Count products in the category
            ->with(['products' => function ($query) {
                $query->withCount('b2bProductReview'); // Count reviews for each product
            }])
            ->where('featured', 1)
            ->take(10)
            ->get();
        $data = B2BCategoryResource::collection($categories);
        return $this->success($data, "Categories");
    }
    public function getCategoryProducts()
    {
        $categories = B2BProductCategory::select('id', 'name', 'slug', 'image')
            ->with(['products.b2bProductReview', 'products.b2bLikes', 'subcategory'])
            ->withCount('products')
            ->with(['products' => function ($query) {
                $query->withCount('b2bProductReview'); // Count reviews for each product
            }])
            ->get();
        $data = B2BCategoryResource::collection($categories);
        return $this->success($data, "Categories products");
    }

    public function bestSelling()
    {
        $bestSellingProducts = B2bOrder::with([
            'product:id,name,front_image,unit_price,slug,default_currency',
            'product.b2bProductReview:id,product_id,rating',
            'b2bProductReview'
        ])
            ->select(
                'id',
                'product_id',
                DB::raw('SUM(product_quantity) as total_sold')
            )
            ->where('status', OrderStatus::DELIVERED)
            ->groupBy('product_id', 'id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();


        $data = B2BBestSellingProductResource::collection($bestSellingProducts);
        return $this->success($data, "Best selling products");
    }

    public function featuredProduct()
    {
        $countryId = request()->query('country_id');

        $query = B2BProduct::with([
            'category',
            'subCategory',
            'shopCountry',
            'orders',
            'b2bProductReview',
        ])
            ->where('status', ProductStatus::ACTIVE);

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $featuredProducts = $query->limit(8)->get();

        $data = B2BSellerProductResource::collection($featuredProducts);

        return $this->success($data, "Featured products");
    }

    public function searchProduct(): array
    {
        $searchQuery = request()->input('search');
        $products = B2BProduct::with([
            'country',
            'b2bProductReview',
            'b2bLikes',
            'b2bProductImages',
            'category',
            'subCategory',
            'user'
        ])
            ->where('name', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('unit_price', 'LIKE', '%' . $searchQuery . '%')
            ->get();

        $data = B2BProductResource::collection($products);

        return [
            'status' => 'true',
            'message' => 'Products filtered',
            'data' => $data,
        ];
    }

    public function categoryBySlug($slug)
    {
        $category = B2bProductCategory::with(['products' => function ($query): void {
            $query->where('status', ProductStatus::ACTIVE);
        }])
            ->select('id', 'name', 'slug', 'image')
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->success($category, 'Products by category');
    }

    public function getProductDetail($slug)
    {
        $product = B2BProduct::with(['category', 'user', 'b2bLikes', 'country', 'b2bProductImages', 'b2bProductReview'])
            ->where('slug', $slug)
            ->firstOrFail();

        $quote_count = B2bQuote::where('product_id', $product->id)->count();

        $b2bProductReview = B2bProdctReview::with(['user' => function ($query): void {
            $query->select('id', 'first_name', 'last_name')
                ->where('type', UserType::B2B_BUYER);
        }])
            ->where('product_id', $product->id)
            ->get();

        $moreFromSeller = B2BProduct::with(['category', 'user', 'b2bLikes', 'subCategory', 'country', 'b2bProductImages', 'b2bProductReview'])
            ->where('user_id', $product->user_id)->get();

        $relatedProducts = B2BProduct::with(['category', 'user', 'b2bLikes', 'subCategory', 'country', 'b2bProductImages', 'b2bProductReview'])
            ->where('category_id', $product->category_id)->get();

        $data = new B2BProductResource($product);

        $response = [
            'data' => $data,
            'reviews' => $b2bProductReview,
            'other_people' => $quote_count,
            'more_from_seller' => B2BProductResource::collection($moreFromSeller),
            'related_products' => B2BProductResource::collection($relatedProducts),
        ];

        return $this->success($response, 'Product Details');
    }

    //Quotes
    public function allQuotes()
    {
        $userId = userAuthId();
        $quotes = B2bQuote::with(['product', 'b2bProductReview'])->where('buyer_id', $userId)
            ->latest('id')
            ->get();
        $data = B2BQuoteResource::collection($quotes);
        return $this->success($data, 'quotes lists');
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
            foreach ($quotes as $quote) {
                if (empty($quote->product_data['unit_price']) || empty($quote->qty)) {
                    throw new \Exception('Invalid product data for quote ID: ' . $quote->id);
                }

                Rfq::create([
                    'buyer_id' => $quote->buyer_id,
                    'seller_id' => $quote->seller_id,
                    'quote_no' => strtoupper(Str::random(10) . $userId),
                    'product_id' => $quote->product_id,
                    'product_quantity' => $quote->qty,
                    'total_amount' => $quote->product_data['unit_price'] * $quote->qty,
                    'p_unit_price' => $quote->product_data['unit_price'],
                    'product_data' => $quote->product_data,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            B2bQuote::where('buyer_id', $userId)->delete();

            DB::commit();

            return $this->success(null, 'RFQ sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(null, 'transaction failed, please try again: ' . $e->getMessage(), 500);
        }
    }

    public function sendRfq($data)
    {
        $quote = B2bQuote::findOrFail($data->rfq_id);

        try {
            $amount = total_amount($quote->product_data['unit_price'], $quote->qty);
            Rfq::create([
                'buyer_id' => $quote->buyer_id,
                'seller_id' => $quote->seller_id,
                'quote_no' => strtoupper(Str::random(10) . userAuthId()),
                'product_id' => $quote->product_id,
                'product_quantity' => $quote->qty,
                'total_amount' => $amount,
                'p_unit_price' => $quote->product_data['unit_price'],
                'product_data' => $quote->product_data,
            ]);

            $quote->delete();

            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            return $this->error(null, 'transaction failed, please try again: ' . $e->getMessage(), 500);
        }
    }

    public function removeQuote($id)
    {
        $quote = B2bQuote::findOrFail($id);
        $quote->delete();

        return $this->success(null, 'Item removed successfully');
    }

    public function sendQuote($data)
    {
        $userId = userAuthId();
        $product = B2BProduct::findOrFail($data->product_id);
        if ($product->availability_quantity < 1) {
            return $this->error(null, 'This product is currently not available for purchase', 422);
        }
        $quote = B2bQuote::where('product_id', $product->id)->where('buyer_id', $userId)->first();

        if ($quote) {
            return $this->error(null, 'Product already exist');
        }

        if ($data->qty < $product->minimum_order_quantity) {
            return $this->error(null, 'Your peferred quantity can not be less than the one already set', 422);
        }
        if ($data->qty > $product->availability_quantity) {
            return $this->error(null, 'Your peferred quantity is greater than the availability quantity : ' . $product->availability_quantity, 422);
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

    //dashboard
    public function getDashboardDetails()
    {
        $currentUserId = userAuthId();
        $rfqStats = Rfq::where('buyer_id', $currentUserId);
        $deals = B2bOrder::where('buyer_id', $currentUserId);

        $orderStats = B2bOrder::where('buyer_id', $currentUserId)
            ->where('status', OrderStatus::DELIVERED)
            ->sum('total_amount');

        $uniqueSellersCount = B2bOrder::where(['buyer_id' => $currentUserId, 'status' => OrderStatus::DELIVERED])
            ->distinct('seller_id')
            ->count('seller_id');

        $recentOrders = B2bOrder::with('seller')
            ->where('buyer_id', $currentUserId)
            ->where('status', OrderStatus::PENDING)
            ->latest()
            ->take(10)
            ->get();

        $seven_days_partners = B2bOrder::where(['seller_id' => $currentUserId, 'status' => OrderStatus::DELIVERED])
            ->distinct('buyer_id')
            ->where('created_at', '<=', Carbon::today()->subDays(7))
            ->count('buyer_id');


        $seven_days_orderStats = B2bOrder::where([
            'seller_id' => $currentUserId,
            'status' => OrderStatus::DELIVERED
        ])->where('created_at', '<=', Carbon::today()->subDays(7))->sum('total_amount');

        $data = [
            'total_purchase' => $orderStats,
            'partners' => $uniqueSellersCount,
            'seven_days_sales' => $seven_days_orderStats,
            'seven_days_partners' => $seven_days_partners,
            'rfq_sent' => $rfqStats->where('status', RfqStatus::PENDING)->count() ?? 0,
            'rfq_accepted' => $rfqStats->where('status', RfqStatus::COMPLETED)->count() ?? 0,
            'deals_in_progress' => $deals->where('status', OrderStatus::PENDING)->count() ?? 0,
            'deals_completed' => $deals->where('status', OrderStatus::PENDING)->count() ?? 0,
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

    public function allOrders()
    {
        $userId = userAuthId();

        $orders = B2bOrder::with('seller')->where('buyer_id', $userId)
            ->latest('id')
            ->get();

        if ($orders->isEmpty()) {
            return $this->error(null, 'No record found to send', 404);
        }

        return $this->success($orders, 'orders lists');
    }

    public function orderDetails($id)
    {
        $order = B2bOrder::with(['seller', 'buyer'])->findOrFail($id);
        $data = new B2BOrderResource($order);
        return $this->success($data, 'order details');
    }
    public function rfqDetails($id)
    {
        $rfq = Rfq::with(['seller', 'messages'])->findOrFail($id);

        $messages = RfqMessage::with(['seller', 'buyer'])->where('rfq_id', $rfq->id)->get();
        $data = [
            'rfq' => $rfq,
            'messages' => $messages
        ];

        return $this->success($data, 'rfq details');
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
                'buyer_id' => userAuthId(),
                'p_unit_price' => $data->p_unit_price,
                'preferred_qty' => $rfq->product_quantity,
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
            'status' => OrderStatus::INPROGRESS,
        ]);

        return $this->success($rfq, 'Quote Accepted successfully');
    }

    //send review request to vendor
    public function addReview($data)
    {
        $userId = userAuthId();
        $review = B2bProdctReview::updateOrCreate(
            ['buyer_id' => $userId, 'product_id' => $data->product_id],
            [
                'rating' => $data->rating,
                'title' => $data->title,
                'note' => $data->note,
            ]
        );

        $msg = $review->wasRecentlyCreated ? 'Review Sent successfully' : 'Review Updated successfully';

        return $this->success(null, $msg);
    }

    public function likeProduct($data)
    {
        $userId = userAuthId();
        $like = B2bProdctLike::firstOrNew([
            'buyer_id' => $userId,
            'product_id' => $data->product_id
        ]);

        if ($like->exists) {
            $like->delete();
            return $this->success(null, 'Unliked successfully');
        }

        $like->save();

        return $this->success(null, 'Liked successfully');
    }

    public function addToWishList($data)
    {
        $userId = userAuthId();
        $product = B2BProduct::find($data->product_id);

        if (!$product) {
            return $this->error(null, 'No record found to send', 404);
        }
        if ($product->availability_quantity < 1) {
            return $this->error(null, 'This product is currently not available for purchase', 422);
        }

        $check = B2bWishList::where('product_id', $product->id)->where('user_id', $userId)->first();

        if ($check) {
            return $this->error(null, 'Product already exist');
        }
        B2bWishList::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'qty' => $product->minimum_order_quantity
        ]);

        return $this->success(null, 'Product Added successfully');
    }

    //wish list
    public function myWishList()
    {
        $userId = userAuthId();
        $wishes =  B2bWishList::with(['product', 'b2bProductReview'])
            ->where('user_id', $userId)
            ->latest('id')
            ->get();

        $data = B2BWishListResource::collection($wishes);
        return $this->success($data, 'My Wish List');
    }

    public function removeItem($id)
    {
        $wish = B2bWishList::findOrFail($id);
        $wish->delete();

        return $this->success(null, 'Item Removed');
    }

    public function sendFromWishList($data)
    {
        $quote = B2bWishList::findOrFail($data->id);

        if (!$quote) {
            return $this->error(null, 'No record found', 404);
        }
        $product = B2BProduct::findOrFail($quote->product_id);
        if ($data->qty < $product->minimum_order_quantity) {
            return $this->error(null, 'Your peferred quantity can not be less than the one already set', 422);
        }
        if ($data->qty > $product->availability_quantity) {
            return $this->error(null, 'Your peferred quantity is greater than the availability quantity : ' . $product->availability_quantity, 422);
        }

        try {
            $amount = total_amount($product->unit_price, $data->qty);

            Rfq::create([
                'buyer_id' => $quote->user_id,
                'seller_id' => $product->user_id,
                'quote_no' => strtoupper(Str::random(10) . Auth::user()->id),
                'product_id' => $product->id,
                'product_quantity' => $data->qty,
                'total_amount' => $amount,
                'p_unit_price' => $product->unit_price,
                'product_data' => $product,
            ]);

            $quote->delete();
            return $this->success(null, 'rfq sent successfully');
        } catch (\Exception $e) {
            return $this->error(null, 'transaction failed, please try again: ' . $e->getMessage(), 500);
        }
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
        $user = User::find($auth->id);
        if (!$user) {
            return $this->error(null, 'User does not exist');
        }
        if (!empty($request->email) && User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
            return $this->error(null, "Email already exists.");
        }
        $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'middlename' => $request->middlename ?? $user->middlename,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
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
        }
        return $this->error(null, 'Old Password did not match');
    }
    public function change2FA($data)
    {

        $authUser = userAuth();
        $user = User::where('type', UserType::B2B_BUYER)->findOrFail($authUser->id);
        $user->update([
            'two_factor_enabled' => $data->two_factor_enabled,
        ]);

        return $this->success('Settings updated');
    }

    public function editCompany($request)
    {
        $auth = Auth::user();

        $company = B2bCompany::where('user_id', $auth->id)->first();

        if (!$company) {
            return $this->error(null, 'No company found to update', 404);
        }

        $logo_url = null;
        if ($request->hasFile('logo')) {
            $logo_url = uploadImage($request, 'logo', 'company-logo');
        }

        $company->update([
            'business_name' => $request->business_name ?? $company->business_name,
            'business_phone' => $request->business_phone ?? $company->business_phone,
            'company_size' => $request->company_size ?? $company->company_size,
            'website' => $request->website ?? $company->website,
            'average_spend' => $request->average_spend ?? $company->average_spend,
            'service_type' => $request->service_type ?? $company->service_type,
            'country_id' => $request->country_id ?? $company->country,
            'logo' => $request->hasFile('image') ? $logo_url : $company->logo,
        ]);

        return $this->success(null, "Details Updated successfully");
    }

    public function addShippingAddress($data)
    {
        $currentUserId = userAuthId();
        $address = BuyerShippingAddress::create([
            'user_id' => $currentUserId,
            'address_name' => $data->address_name,
            'name' => $data->name,
            'surname' => $data->surname,
            'email' => $data->email,
            'phone' => $data->phone,
            'street' => $data->street,
            'city' => $data->city,
            'postal_code' => $data->postal_code,
            'state_id' => $data->state_id,
            'country_id' => $data->country_id,
        ]);
        return $this->success($address, 'Address Added');
    }
    public function getAllShippingAddress()
    {
        $currentUserId = userAuthId();
        $addresses = BuyerShippingAddress::with(['state', 'country'])->where('user_id', $currentUserId)->get();
        $data = B2BBuyerShippingAddressResource::collection($addresses);
        return $this->success($data, 'All address');
    }

    public function getShippingAddress($id)
    {
        $address = BuyerShippingAddress::with(['state', 'country'])->findOrFail($id);

        $data = new B2BBuyerShippingAddressResource($address);
        return $this->success($data, 'Address detail');
    }

    public function updateShippingAddress($id, $data)
    {
        $address = BuyerShippingAddress::find($id);
        if (!$address) {
            return $this->error(null, 'No record found', 404);
        }
        $address->update([
            'address_name' => $data->address_name ?? $address->address_name,
            'name' => $data->name ?? $address->name,
            'surname' => $data->surname ?? $address->surname,
            'email' => $data->email ?? $address->email,
            'phone' => $data->phone ?? $address->phone,
            'street' => $data->street ?? $address->street,
            'city' => $data->city ?? $address->city,
            'postal_code' => $data->postal_code ?? $address->postal_code,
            'state_id' => $data->state_id ?? $address->state_id,
            'country_id' => $data->country_id ?? $address->country_id,
        ]);

        return $this->success(null, 'Details Updated successfully');
    }

    public function deleteShippingAddress($id)
    {
        $address = BuyerShippingAddress::find($id);
        if (!$address) {
            return $this->error(null, 'No record found', 404);
        }
        $address->delete();
        return $this->success(null, 'Address Deleted successfully');
    }

    public function setDefaultAddress($id)
    {
        $method = BuyerShippingAddress::find($id);
        if (!$method) {
            return $this->error(null, 'No record found', 404);
        }

        BuyerShippingAddress::where('user_id', userAuthId())
            ->where('is_default', 1)
            ->update(['is_default' => 0]);

        $method->update([
            'is_default' => 1,
        ]);

        return $this->success(null, 'Address Set as default');
    }
}
