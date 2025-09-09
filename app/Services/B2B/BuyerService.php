<?php

namespace App\Services\B2B;

use Carbon\Carbon;
use App\Models\Rfq;
use App\Models\Blog;
use App\Models\User;
use App\Enum\UserType;
use App\Models\Banner;
use App\Enum\RfqStatus;
use App\Models\Payment;
use App\Enum\BannerType;
use App\Enum\UserStatus;
use App\Models\B2bOrder;
use App\Models\B2bQuote;
use App\Enum\OrderStatus;
use App\Enum\ProductType;
use App\Models\B2bBanner;
use App\Models\B2bCompany;
use App\Models\B2BProduct;
use App\Models\ClientLogo;
use App\Models\PageBanner;
use App\Models\RfqMessage;
use App\Enum\ProductStatus;
use App\Models\B2bWishList;
use App\Models\SliderImage;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\B2bProdctLike;
use App\Models\SocialSetting;
use App\Models\B2bProdctReview;
use App\Models\B2BRequestRefund;
use App\Enum\RefundRequestStatus;
use App\Models\B2bProductCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BlogResource;
use App\Models\BuyerShippingAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\BuyerResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\B2BOrderResource;
use App\Http\Resources\B2BQuoteResource;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\B2BBannerResource;
use App\Http\Resources\B2BProductResource;
use App\Http\Resources\ClientLogoResource;
use App\Http\Resources\SocialLinkResource;
use App\Http\Resources\B2BCategoryResource;
use App\Http\Resources\B2BWishListResource;
use App\Http\Resources\B2BBestSellingProductResource;
use App\Http\Resources\B2BBuyerShippingAddressResource;

class BuyerService
{
    use HttpResponse;

    // Admin section
    public function allCustomers()
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

        return $this->withPagination($data, "All Buyers");
    }

    public function viewCustomer($id)
    {
        $user = User::with([
            'userCountry',
            'state',
            'wishlist.product',
            'payments.order',
        ])
            ->where('type', UserType::B2B_BUYER)
            ->where('id', $id)
            ->firstOrFail();

        $data = new CustomerResource($user);

        return $this->success($data, 'Buyer details');
    }

    public function banCustomer($request)
    {
        $user = User::where('type', UserType::B2B_BUYER)
            ->where('id', $request->user_id)
            ->firstOrFail();

        $user->status = UserStatus::BLOCKED;
        $user->is_admin_approve = 0;

        $user->save();

        return $this->success(null, 'User has been blocked successfully');
    }

    public function removeCustomer($request)
    {
        DB::transaction(function () use ($request): void {
            User::whereIn('id', $request->user_ids)
                ->update([
                    'status' => UserStatus::DELETED,
                    'is_verified' => 0,
                    'is_admin_approve' => 0,
                ]);

            User::whereIn('id', $request->user_ids)->delete();
        });

        return $this->success(null, 'User(s) have been removed successfully');
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

        $image = $request->hasFile('image') ?
            uploadUserImage($request, 'image', $user) :
            ['image' => null, 'public_id' => null];

        $user->update([
            'image' => $image['url'],
            'public_id' => $image['public_id'],
        ]);

        return $this->success(null, 'User has been created successfully', 201);
    }

    public function editCustomer($request)
    {
        $user = User::where('type', 'customer')
            ->find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $image = $request->hasFile('image') ?
            uploadUserImage($request, 'image', $user) :
            ['image' => $user->image, 'public_id' => $user->public_id];

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'middlename' => $request->middlename,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'image' => $image['url'],
            'public_id' => $image['public_id'],
            'status' => $request->status,
        ]);

        return $this->success(null, 'User has been updated successfully');
    }

    public function getPayment($id)
    {
        $payment = Payment::with(['user', 'order'])->findOrFail($id);
        $data = new PaymentResource($payment);

        return $this->success($data, 'Payment detail');
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

    public function getClientLogos()
    {
        $clients = ClientLogo::latest()->get();

        return $this->success(ClientLogoResource::collection($clients), 'Client Brands');
    }

    public function getSocialLinks()
    {
        $links = SocialSetting::latest()->get();

        return $this->success(SocialLinkResource::collection($links), 'Social links');
    }

    public function promoBanners()
    {
        $banners = Banner::where('type', BannerType::B2B)->latest()->get();

        return $this->success(B2BBannerResource::collection($banners), 'Banners');
    }

    public function getSliders()
    {
        $sliders = SliderImage::where('type', BannerType::B2B)
            ->latest()
            ->get();

        return $this->success(SliderResource::collection($sliders), 'banners');
    }

    public function getPageBanners($page)
    {
        $banners = PageBanner::select('id', 'page', 'section', 'type', 'banner_url')
            ->where('type', BannerType::B2B)
            ->where('page', $page)
            ->get();

        return $this->success($banners, 'home-banners');
    }

    public function getAgriEcomProducts()
    {
        $products = B2BProduct::with([
            'category',
            'user',
            'b2bLikes',
            'b2bProductReview.user',
            'subCategory',
            'country',
            'b2bProductImages',
        ])
            ->whereStatus(ProductStatus::ACTIVE)
            ->where('type', ProductType::AgriEcom)
            ->latest()
            ->get();

        return $this->success(B2BProductResource::collection($products), 'Products');
    }

    public function searchAgriEcomProducts()
    {
        $searchQuery = request()->input('search');

        $products = B2BProduct::with([
            'country',
            'b2bProductReview.user',
            'b2bLikes',
            'b2bProductImages',
            'category',
            'subCategory',
            'user',
        ])
            ->where('type', ProductType::AgriEcom)
            ->where(function ($query) use ($searchQuery) {
                $query->where('name', 'LIKE', '%' . $searchQuery . '%')
                    ->orWhere('unit_price', 'LIKE', '%' . $searchQuery . '%');
            })
            ->get();

        return $this->success(B2BProductResource::collection($products), 'Products filtered');
    }

    public function getProducts()
    {
        $products = B2BProduct::with([
            'category',
            'user',
            'b2bLikes',
            'b2bProductReview.user',
            'subCategory',
            'country',
            'b2bProductImages',
        ])
            ->whereStatus(ProductStatus::ACTIVE)
            ->get();

        return $this->success(B2BProductResource::collection($products), 'Products');
    }

    public function categories()
    {
        $categories = B2bProductCategory::with(['subcategory', 'products', 'products.b2bProductReview', 'products.b2bLikes'])
            ->withCount('products') // Count products in the category
            ->with(['products' => function ($query): void {
                $query->withCount('b2bProductReview'); // Count reviews for each product
            }])
            ->where('featured', 1)
            ->take(10)
            ->get();

        return $this->success(B2BCategoryResource::collection($categories), 'Categories');
    }

    public function allBlogs()
    {
        $blogs = Blog::with('user')->where('type', BannerType::B2B)->latest()->get();

        return $this->success(BlogResource::collection($blogs), 'Blogs');
    }

    public function singleBlog($slug)
    {
        $blog = Blog::with('user')->where('type', BannerType::B2B)->where('slug', $slug)->firstOrFail();

        return $this->success(new BlogResource($blog), 'Blog details');
    }

    public function getCategoryProducts()
    {
        $categories = B2BProductCategory::select('id', 'name', 'slug', 'image')
            ->with(['products.b2bProductReview', 'products.b2bLikes', 'subcategory'])
            ->withCount('products')
            ->with(['products' => function ($query): void {
                $query->withCount('b2bProductReview'); // Count reviews for each product
            }])
            ->get();

        return $this->success(B2BCategoryResource::collection($categories), 'Categories products');
    }

    public function bestSelling()
    {
        $bestSellingProducts = B2bOrder::with([
            'product:id,name,front_image,unit_price,slug,default_currency',
            'product.b2bProductReview:id,product_id,rating',
            'b2bProductReview',
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

        return $this->success(B2BBestSellingProductResource::collection($bestSellingProducts), 'Best selling products');
    }


    public function featuredProduct()
    {
        $countryId = request()->query('country_id');

        $type = request()->query('type');

        $featuredProducts = B2BProduct::with([
            'shopCountry',
            'orders',
            'b2bProductReview.user',
            'category',
            'user',
            'b2bLikes',
            'subCategory',
            'country',
            'b2bProductImages',
        ])
            ->where('status', ProductStatus::ACTIVE)
            ->when($countryId, fn($q) => $q->where('country_id', $countryId))
            ->when($type, fn($q) => $q->where('type', $type))
            ->limit(8)
            ->get();

        return $this->success(B2BProductResource::collection($featuredProducts), 'Featured products');
    }


    public function searchProduct()
    {
        $searchQuery = request()->input('search');

        $products = B2BProduct::with([
            'country',
            'b2bProductReview.user',
            'b2bLikes',
            'b2bProductImages',
            'category',
            'subCategory',
            'user',
        ])
            ->where('name', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('unit_price', 'LIKE', '%' . $searchQuery . '%')
            ->get();

        return $this->success(B2BProductResource::collection($products), 'Products filtered');
    }

    public function categoryBySlug($slug)
    {
        $category = B2bProductCategory::with(['subcategory', 'products', 'products.b2bProductReview', 'products.b2bLikes'])
            ->withCount('products')
            ->with(['products' => function ($query): void {
                $query->withCount('b2bProductReview');
            }])
            ->select('id', 'name', 'slug', 'image')
            ->where(['featured' => 1, 'slug' => $slug])
            ->firstOrFail();

        return $this->success(new B2BCategoryResource($category), 'Products by category');
    }

    public function getProductDetail($slug)
    {
        $product = B2BProduct::with([
            'category',
            'user',
            'b2bLikes',
            'country',
            'b2bProductImages',
            'b2bProductReview.user' => function ($query): void {
                $query->select('id', 'first_name', 'last_name')
                    ->where('type', UserType::B2B_BUYER);
            },
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $quote_count = B2bQuote::where('product_id', $product->id)->count();

        $moreFromSeller = B2BProduct::with([
            'category',
            'user',
            'b2bLikes',
            'subCategory',
            'country',
            'b2bProductImages',
            'b2bProductReview.user',
        ])
            ->where('user_id', $product->user_id)
            ->get();

        $relatedProducts = B2BProduct::with([
            'category',
            'user',
            'b2bLikes',
            'subCategory',
            'country',
            'b2bProductImages',
            'b2bProductReview.user',
        ])
            ->where('category_id', $product->category_id)
            ->get();

        $data = new B2BProductResource($product);

        $response = [
            'data' => $data,
            'reviews' => $product->b2bProductReview,
            'other_people' => $quote_count,
            'more_from_seller' => B2BProductResource::collection($moreFromSeller),
            'related_products' => B2BProductResource::collection($relatedProducts),
        ];

        return $this->success($response, 'Product Details');
    }

    // Quotes
    public function allQuotes()
    {
        $userId = userAuthId();
        $quotes = B2bQuote::with(['product', 'b2bProductReview'])
            ->withCount('b2bProductReview')
            ->where('buyer_id', $userId)
            ->latest()
            ->get();

        return $this->success(B2BQuoteResource::collection($quotes), 'quotes lists');
    }

    public function sendMutipleQuotes()
    {
        $userId = userAuthId();

        $quotes = B2bQuote::where('buyer_id', $userId)->latest()->get();

        if ($quotes->isEmpty()) {
            return $this->error(null, 'No record found to send', 404);
        }

        DB::beginTransaction();

        try {
            foreach ($quotes as $quote) {
                if (empty($quote->product_data['unit_price'])) {
                    continue;
                }
                if (empty($quote->qty)) {
                    continue;
                }
                $product = B2BProduct::findOrFail($quote->product_id);
                $unit_price = currencyConvert(
                    userAuth()->default_currency,
                    $quote->product_data['unit_price'],
                    $product->shopCountry->currency ?? 'USD',
                );

                Rfq::create([
                    'buyer_id' => $quote->buyer_id,
                    'seller_id' => $quote->seller_id,
                    'quote_no' => strtoupper(Str::random(10) . $userId),
                    'product_id' => $quote->product_id,
                    'product_quantity' => $quote->qty,
                    'total_amount' => $unit_price * $quote->qty,
                    'p_unit_price' => $unit_price,
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

    public function sendRfq($request)
    {
        $quote = B2bQuote::findOrFail($request->rfq_id);

        try {
            $product = B2BProduct::findOrFail($quote->product_id);

            $unit_price = currencyConvert(
                userAuth()->default_currency,
                $quote->product_data['unit_price'],
                $product->shopCountry->currency ?? 'USD',
            );

            $amount = total_amount($unit_price, $quote->qty);

            Rfq::create([
                'buyer_id' => $quote->buyer_id,
                'seller_id' => $quote->seller_id,
                'quote_no' => strtoupper(Str::random(10) . userAuthId()),
                'product_id' => $quote->product_id,
                'product_quantity' => $quote->qty,
                'total_amount' => $amount,
                'p_unit_price' => $unit_price,
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

    public function sendQuote($request)
    {
        $userId = userAuthId();

        $product = B2BProduct::findOrFail($request->product_id);

        if ($product->availability_quantity < 1) {
            return $this->error(null, 'This product is currently not available for purchase', 422);
        }

        $quote = B2bQuote::where('product_id', $product->id)
            ->where('buyer_id', $userId)
            ->exists();

        if ($quote) {
            return $this->error(null, 'Product already exist');
        }

        if ($request->qty < $product->minimum_order_quantity) {
            return $this->error(null, 'Your peferred quantity can not be less than the one already set', 422);
        }

        if ($request->qty > $product->availability_quantity) {
            return $this->error(null, 'Your peferred quantity is greater than the availability quantity : ' . $product->availability_quantity, 422);
        }

        $quote = B2bQuote::create([
            'buyer_id' => userAuthId(),
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
            'product_data' => $product,
            'qty' => $request->qty,
        ]);

        return $this->success($quote, 'quote Added successfully');
    }

    // dashboard
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
            'status' => OrderStatus::DELIVERED,
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

        return $this->success($data, 'Dashboard details');
    }

    public function allRfqs()
    {
        $userId = userAuthId();

        $rfqs = Rfq::with('seller')->where('buyer_id', $userId)
            ->latest()
            ->get();

        return $this->success($rfqs, 'rfqs lists');
    }

    public function allOrders()
    {
        $searchQuery = request()->input('search');

        $orders = B2bOrder::with('seller')->where('buyer_id', userAuthId())->when($searchQuery, function ($queryBuilder) use ($searchQuery): void {
            $queryBuilder->where(function ($subQuery) use ($searchQuery): void {
                $subQuery->where('buyer_id', userAuthId())
                    ->where('order_no', 'LIKE', '%' . $searchQuery . '%');
            });
        })->get();

        return $this->success($orders, 'orders lists');
    }

    public function orderDetails($id)
    {
        $order = B2bOrder::with(['seller', 'buyer'])
            ->where('buyer_id', userAuthId())
            ->where('id', $id)
            ->firstOrFail();

        return $this->success(new B2BOrderResource($order), 'order details');
    }

    public function rfqDetails($id)
    {
        $rfq = Rfq::with(['seller', 'messages'])->where('buyer_id', userAuthId())->findOrFail($id);

        $messages = RfqMessage::with(['seller', 'buyer'])->where('rfq_id', $rfq->id)->get();
        $data = [
            'rfq' => $rfq,
            'messages' => $messages,
        ];

        return $this->success($data, 'rfq details');
    }

    // send review request to vendor
    public function sendReviewRequest($request)
    {
        $rfq = Rfq::find($request->rfq_id);

        if (! $rfq) {
            return $this->error(null, 'No record found', 404);
        }

        DB::beginTransaction();

        try {

            $rfq->messages()->create([
                'rfq_id' => $request->rfq_id,
                'buyer_id' => userAuthId(),
                'p_unit_price' => $request->p_unit_price,
                'preferred_qty' => $rfq->product_quantity,
                'note' => $request->note,
            ]);

            $rfq->update(['status' => 'review']);
            DB::commit();

            return $this->success($rfq, 'Review sent successfully with details.');
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->error(null, 'Failed to send review request: ' . $e->getMessage(), 500);
        }
    }

    // send review request to vendor
    public function acceptQuote($request)
    {
        $rfq = Rfq::find($request->rfq_id);

        if (! $rfq) {
            return $this->error(null, 'No record found', 404);
        }

        $rfq->update([
            'status' => OrderStatus::INPROGRESS,
        ]);

        return $this->success($rfq, 'Quote Accepted successfully');
    }

    // send review request to vendor
    public function addReview($request)
    {
        $userId = userAuthId();

        $review = B2bProdctReview::updateOrCreate(
            [
                'buyer_id' => $userId,
                'product_id' => $request->product_id,
            ],
            [
                'rating' => $request->rating,
                'title' => $request->title,
                'note' => $request->note,
            ]
        );

        $msg = $review->wasRecentlyCreated ? 'Review Sent successfully' : 'Review Updated successfully';

        return $this->success(null, $msg);
    }

    public function likeProduct($request)
    {
        $userId = userAuthId();
        $like = B2bProdctLike::firstOrNew([
            'buyer_id' => $userId,
            'product_id' => $request->product_id,
        ]);

        if ($like->exists) {
            $like->delete();

            return $this->success(null, 'Unliked successfully');
        }

        $like->save();

        return $this->success(null, 'Liked successfully');
    }

    public function addToWishList($request)
    {
        $userId = userAuthId();

        $product = B2BProduct::find($request->product_id);

        if (! $product) {
            return $this->error(null, 'No record found', 404);
        }

        if ($product->availability_quantity < 1) {
            return $this->error(null, 'This product is currently not available for purchase', 422);
        }

        $check = B2bWishList::where('product_id', $product->id)
            ->where('user_id', $userId)
            ->exists();

        if ($check) {
            return $this->error(null, 'Product already exist', 400);
        }

        B2bWishList::create([
            'user_id' => $userId,
            'product_id' => $product->id,
            'qty' => $product->minimum_order_quantity,
        ]);

        return $this->success(null, 'Product Added successfully');
    }

    // wish list
    public function myWishList()
    {
        $userId = userAuthId();
        $wishes = B2bWishList::with(['product', 'b2bProductReview'])
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return $this->success(B2BWishListResource::collection($wishes), 'My Wish List');
    }

    public function removeItem($id)
    {
        $wish = B2bWishList::findOrFail($id);

        $wish->delete();

        return $this->success(null, 'Item Removed');
    }

    public function sendFromWishList($request)
    {
        $quote = B2bWishList::findOrFail($request->id);

        if (! $quote) {
            return $this->error(null, 'No record found', 404);
        }

        $product = B2BProduct::findOrFail($quote->product_id);

        if ($request->qty < $product->minimum_order_quantity) {
            return $this->error(null, 'Your peferred quantity can not be less than the one already set', 422);
        }

        if ($request->qty > $product->availability_quantity) {
            return $this->error(null, 'Your peferred quantity is greater than the availability quantity : ' . $product->availability_quantity, 422);
        }

        try {
            $amount = total_amount($product->unit_price, $request->qty);

            Rfq::create([
                'buyer_id' => $quote->user_id,
                'seller_id' => $product->user_id,
                'quote_no' => strtoupper(Str::random(10) . Auth::user()->id),
                'product_id' => $product->id,
                'product_quantity' => $request->qty,
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

    // Account section
    public function profile()
    {
        $auth = userAuth();

        $user = User::with('b2bCompany')
            ->where('id', $auth->id)
            ->first();

        if (! $user) {
            return $this->error(null, 'User does not exist', 404);
        }

        return $this->success(new BuyerResource($user), 'Buyer profile');
    }

    public function editAccount($request)
    {
        $auth = Auth::user();

        $user = User::find($auth->id);

        if (! $user) {
            return $this->error(null, 'User does not exist', 404);
        }

        if (
            ! empty($request->email) && User::where('email', $request->email)
            ->where('id', '!=', $user->id)
            ->exists()
        ) {
            return $this->error(null, 'Email already exists.');
        }

        $image = $request->hasFile('image') ?
            uploadUserImage($request, 'image', $user) :
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

        return $this->success(null, 'Profile Updated successfully');
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

        return $this->error(null, 'Old Password do not match');
    }

    public function change2FA($data)
    {
        $authUser = userAuth();

        $user = User::where('id', $authUser->id)->firstOrFail();

        $user->update([
            'two_factor_enabled' => $data->two_factor_enabled,
        ]);

        return $this->success('Settings updated');
    }

    public function editCompany($request)
    {
        $auth = Auth::user();

        $company = B2bCompany::where('user_id', $auth->id)->first();

        if (! $company) {
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
            'logo' => $request->hasFile('image') ? $logo_url['url'] : $company->logo,
        ]);

        return $this->success(null, 'Details Updated successfully');
    }

    public function addShippingAddress($request)
    {
        $currentUserId = userAuthId();

        $address = BuyerShippingAddress::create([
            'user_id' => $currentUserId,
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
        ]);

        return $this->success($address, 'Address Added');
    }

    public function getAllShippingAddress()
    {
        $currentUserId = userAuthId();

        $addresses = BuyerShippingAddress::with(['state', 'country'])->where('user_id', $currentUserId)->latest()->get();

        return $this->success(B2BBuyerShippingAddressResource::collection($addresses), 'All address');
    }

    public function getShippingAddress($id)
    {
        $address = BuyerShippingAddress::with(['state', 'country'])->findOrFail($id);

        return $this->success(new B2BBuyerShippingAddressResource($address), 'Address detail');
    }

    public function updateShippingAddress($request, $id)
    {
        $address = BuyerShippingAddress::findOrFail($id);

        $address->update([
            'address_name' => $request->address_name ?? $address->address_name,
            'name' => $request->name ?? $address->name,
            'surname' => $request->surname ?? $address->surname,
            'email' => $request->email ?? $address->email,
            'phone' => $request->phone ?? $address->phone,
            'street' => $request->street ?? $address->street,
            'city' => $request->city ?? $address->city,
            'postal_code' => $request->postal_code ?? $address->postal_code,
            'state_id' => $request->state_id ?? $address->state_id,
            'country_id' => $request->country_id ?? $address->country_id,
        ]);

        return $this->success(null, 'Details Updated successfully');
    }

    public function deleteShippingAddress($id)
    {
        $address = BuyerShippingAddress::findOrFail($id);

        $address->delete();

        return $this->success(null, 'Address Deleted successfully');
    }

    public function setDefaultAddress($id)
    {
        $method = BuyerShippingAddress::findOrFail($id);

        BuyerShippingAddress::where('user_id', userAuthId())
            ->where('is_default', 1)
            ->update(['is_default' => 0]);

        $method->update([
            'is_default' => 1,
        ]);

        return $this->success(null, 'Address Set as default');
    }
}
