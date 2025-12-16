<?php

namespace App\Services;

use App\Enum\BannerStatus;
use App\Enum\BannerType;
use App\Enum\OrderStatus;
use App\Enum\ProductReviewStatus;
use App\Enum\ProductStatus;
use App\Enum\ProductType;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\SellerDetailResource;
use App\Http\Resources\SellerProductResource;
use App\Http\Resources\SingleProductResource;
use App\Models\Action;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Deal;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Trait\General;
use App\Trait\HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class HomeService
{
    use General, HttpResponse;

    public function bestSelling(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->query('country_id');
        $type = $request->query('type', ProductType::B2C->value);

        $query = Product::with('shopCountry')
            ->select(
                'products.id',
                'products.name',
                'products.slug',
                'products.image',
                'products.price',
                'products.description',
                'products.category_id',
                'products.country_id',
                DB::raw('COUNT(order_items.id) as total_orders')
            )
            ->leftJoin('order_items', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->groupBy(
                'products.id',
                'products.name',
                'products.price',
                'products.slug',
                'products.image',
                'products.description',
                'products.category_id',
                'products.country_id'
            )
            ->when($countryId, fn ($q) => $q->where('orders.country_id', $countryId))
            ->when(
                $type,
                fn ($q) => $q->where('products.type', $type),
                fn ($q) => $q->where('products.type', '!=', 'agriecom')
            )
            ->orderBy('total_orders', 'DESC')
            ->take(10);

        $products = $query->get();

        $products->each(function ($product): void {
            $product->currency = $product->shopCountry->currency ?? null;
            unset($product->shopCountry);
        });

        return $this->success($products, 'Best selling products');
    }

    public function allProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->query('country_id');
        $type = $request->query('type', ProductType::B2C->value);

        $allProducts = Product::with([
            'shopCountry',
            'productimages',
            'category',
            'subCategory',
            'brand',
            'color',
            'unit',
            'size',
            'orders',
            'productReviews',
            'productVariations' => function ($query): void {
                $query->select('id', 'product_id', 'variation', 'sku', 'price', 'stock', 'image');
            },
        ])
            ->where('status', ProductStatus::ACTIVE)
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            }, function ($q) {
                $q->where('type', '!=', 'agriecom');
            })
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->paginate(25);

        $data = SellerProductResource::collection($allProducts);

        return $this->withPagination($data, 'All products');
    }

    public function featuredProduct(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->query('country_id');
        $type = $request->query('type', ProductType::B2C->value);

        $featuredProducts = Product::with([
            'category',
            'subCategory',
            'shopCountry',
            'brand',
            'color',
            'unit',
            'size',
            'orders',
            'productReviews',
            'productVariations' => function ($query): void {
                $query->select('id', 'product_id', 'variation', 'sku', 'price', 'stock', 'image');
            },
        ])
            ->where('is_featured', true)
            ->where('status', ProductStatus::ACTIVE)
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            }, function ($q) {
                $q->where('type', '!=', 'agriecom');
            })
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->limit(8)
            ->get();

        $data = SellerProductResource::collection($featuredProducts);

        return $this->success($data, 'Featured products');
    }

    public function topProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->query('country_id');
        $type = $request->query('type', ProductType::B2C->value);

        $topProducts = Product::with([
            'category',
            'subCategory',
            'shopCountry',
            'brand',
            'color',
            'unit',
            'size',
            'orders',
            'productReviews',
            'productVariations' => function ($query): void {
                $query->select('id', 'product_id', 'variation', 'sku', 'price', 'stock', 'image');
            },
        ])
            ->where('status', ProductStatus::ACTIVE)
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            }, function ($q) {
                $q->where('type', '!=', 'agriecom');
            })
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->latest()
            ->limit(10)
            ->get();

        $data = SellerProductResource::collection($topProducts);

        return $this->success($data, 'Top products');
    }

    public function pocketFriendly(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->query('country_id');
        $type = $request->query('type', ProductType::B2C->value);

        $query = Product::with([
            'category',
            'subCategory',
            'shopCountry',
            'brand',
            'color',
            'unit',
            'size',
            'orders',
            'productReviews',
            'productVariations' => function ($query): void {
                $query->select('id', 'product_id', 'variation', 'sku', 'price', 'stock', 'image');
            },
        ])
            ->when($type, function ($q) use ($type) {
                $q->where('type', $type);
            }, function ($q) {
                $q->where('type', '!=', 'agriecom');
            })
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId));

        $query->whereBetween('price', [2, 10000])
            ->where('status', ProductStatus::ACTIVE)
            ->orderBy('price', 'asc')
            ->limit(4);

        $products = $query->get();

        $data = SellerProductResource::collection($products);

        return $this->success($data, 'Pocket friendly products');
    }

    public function productSlug(string $slug): JsonResponse
    {
        $product = Product::with([
            'brand',
            'category',
            'subCategory',
            'color',
            'unit',
            'size',
            'productReviews.user',
            'productimages',
            'shopCountry',
            'productVariations',
            'user.userCountry' => function ($query): void {
                $query->with('shopCountry:country_id,flag');
            },
        ])
            ->withCount('productReviews')
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            return $this->error(null, 'Product not found', 404);
        }

        $data = new SingleProductResource($product);

        return $this->success($data, 'Product detail');
    }

    public function topBrands(): JsonResponse
    {
        $brands = Brand::select(['id', 'name', 'slug', 'image'])
            ->where('status', 'active')
            ->latest()
            ->take(8)
            ->get();

        return $this->success($brands, 'Top brands');
    }

    public function topSellers(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->input('country_id');

        $topSellersQuery = User::select(
            DB::raw('users.id as user_id, users.uuid, CONCAT(users.first_name, " ", users.last_name) as name, users.image as image, COUNT(order_items.id) as total_sales')
        )
            ->join('products', 'users.id', '=', 'products.user_id')
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->groupBy('users.id', 'users.uuid', 'users.first_name', 'users.last_name', 'users.image')
            ->orderByDesc('total_sales');

        if ($countryId) {
            $topSellersQuery->where('orders.country_id', $countryId);
        } else {
            $topSellersQuery->limit(8);
        }

        $topSellers = $topSellersQuery->get();

        return $this->success($topSellers, 'Top sellers');
    }

    public function categorySlug(\Illuminate\Http\Request $request, string $slug): JsonResponse
    {
        $countryId = $request->query('country_id', 231);

        $category = Category::select('id', 'name', 'slug', 'image')
            ->where('slug', $slug)
            ->first();

        if (! $category) {
            return $this->error(null, 'Category not found', 404);
        }

        $products = Product::where('category_id', $category->id)
            ->where('status', ProductStatus::ACTIVE)
            ->when($countryId, fn ($q) => $q->where('country_id', $countryId))
            ->select('id', 'name', 'slug', 'price', 'image', 'category_id', 'discount_price', 'default_currency')
            ->withCount('productReviews as total_reviews')
            ->withAvg('productReviews as average_rating', 'rating')
            ->get()
            ->map(fn ($product) => tap($product, function ($p) {
                $p->average_rating = $p->average_rating ? round($p->average_rating, 1) : 0;
            }));

        return $this->success($products, 'Products by category');
    }

    public function recommendedProducts(\Illuminate\Http\Request $request): JsonResponse
    {
        $countryId = $request->query('country_id', 231);
        $type = $request->query('type', ProductType::B2C->value);

        $products = Product::where('status', ProductStatus::ACTIVE)
            ->when($countryId, function ($query) use ($countryId): void {
                $query->where('country_id', $countryId);
            })
            ->when($type, function ($query) use ($type): void {
                $query->where('type', $type);
            }, function ($query): void {
                $query->where('type', '!=', ProductType::AgriEcom->value);
            })
            ->select(['id', 'name', 'slug', 'description', 'discount_price', 'price', 'image', 'country_id'])
            ->with(['shopCountry' => function ($query): void {
                $query->select('country_id', 'currency');
            }])
            ->get()
            ->shuffle()
            ->take(6);

        $products->each(function ($product): void {
            $product->currency = $product->shopCountry->currency ?? null;
            unset($product->shopCountry);
        });

        return $this->success($products, 'Recommended products');
    }

    public function productReview($request): JsonResponse
    {
        $user = User::findOrFail($request->user_id);

        $product = Product::with('productReviews')
            ->findOrFail($request->product_id);

        $product->productReviews()->create([
            'user_id' => $request->user_id,
            'rating' => $request->rating,
            'review' => $request->review,
            'status' => ProductReviewStatus::APPROVED,
        ]);

        $actionSlug = Action::whereIn('name', ['Write a product review', 'Product review'])
            ->orWhere('slug', 'write_a_product_review')
            ->value('slug');

        reward_user($user, $actionSlug, 'completed');

        return $this->success(null, 'Review added successfully');
    }

    public function saveForLater($request): JsonResponse
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id) {
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with('wishlist')->findOrFail($request->user_id);
        $product = Product::findOrFail($request->product_id);

        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            return $this->error(null, 'Product already in wishlist', 409);
        }

        $user->wishlist()->create([
            'product_id' => $product->id,
        ]);

        return $this->success(null, 'Product saved for later');
    }

    public function sellerInfo($request, $uuid): JsonResponse
    {
        $search = $request->input('search');

        $user = User::with([
            'products' => function ($query) use ($search): void {
                if ($search) {
                    $query->where(function ($q) use ($search): void {
                        $q->where('name', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%');
                    });
                }
                $query->with(['category', 'subCategory']);
                $query->withCount(['productReviews']);
                $query->withCount(['orders as item_sold' => function ($query): void {
                    $query->where('orders.status', OrderStatus::DELIVERED);
                }]);
            },
        ])->where('uuid', $uuid)
            ->withCount('products')
            ->first();

        if (! $user) {
            return $this->error(null, 'Not found', 400);
        }

        $data = new SellerDetailResource($user);

        return $this->success($data, 'Seller details');
    }

    public function sellerCategory($request, $uuid): JsonResponse
    {
        $search = $request->input('search');

        $user = User::where('uuid', $uuid)
            ->with([
                'products' => function ($query) use ($search): void {
                    $query->with(['category' => function ($q) use ($search): void {
                        $q->select(['id', 'name', 'slug', 'image'])
                            ->when($search, fn ($q) => $q->whereLike('name', "%{$search}%"));
                    }]);
                },
            ])
            ->first();

        if (! $user) {
            return $this->error(null, 'Category not found', 404);
        }

        $categories = $user->products
            ->pluck('category')
            ->filter()
            ->unique('id')
            ->values();

        if ($categories->isEmpty()) {
            return $this->error(null, 'No categories found for this seller', 404);
        }

        return $this->success($categories, 'Seller categories');
    }

    public function sellerReviews($request, $uuid): JsonResponse
    {
        $search = $request->input('search');
        $perPage = $request->input('per_page', 4);
        $currentPage = $request->input('page', 1);

        $user = User::where('uuid', $uuid)
            ->with(['products' => function ($query) use ($search): void {
                $query->with(['productReviews' => function ($q) use ($search): void {
                    if ($search) {
                        $q->where('review', 'like', '%'.$search.'%');
                    }

                    $q->with(['user' => function ($userQuery): void {
                        $userQuery->select('id', 'first_name', 'last_name');
                    }]);

                    $q->select('id', 'user_id', 'product_id', 'rating', 'review', 'created_at');
                }]);
            }])
            ->first();

        if (! $user) {
            return $this->error(null, 'Seller not found', 404);
        }

        $reviews = $user->products->pluck('productReviews')->flatten();

        if ($reviews->isEmpty()) {
            return $this->error(null, 'No reviews found for this seller', 404);
        }

        $overallRating = $reviews->avg('rating');

        $currentPageReviews = $reviews->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $paginatedReviews = new LengthAwarePaginator(
            $currentPageReviews,
            $reviews->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $reviewResources = ReviewResource::collection($paginatedReviews);

        $responseData = [
            'reviews' => $reviewResources,
            'overall_rating' => round($overallRating, 1),
            'pagination' => [
                'current_page' => $paginatedReviews->currentPage(),
                'last_page' => $paginatedReviews->lastPage(),
                'per_page' => $paginatedReviews->perPage(),
                'prev_page_url' => $paginatedReviews->previousPageUrl(),
                'next_page_url' => $paginatedReviews->nextPageUrl(),
                'total' => $paginatedReviews->total(),
            ],
        ];

        return $this->success($responseData, 'Seller reviews');
    }

    public function moveToCart($request): JsonResponse
    {
        $itemId = $request->product_id;
        $userId = $request->user_id;

        $wishlistItem = Wishlist::where('user_id', $userId)
            ->where('product_id', $itemId)
            ->first();

        if ($wishlistItem) {
            Cart::updateOrCreate(
                [
                    'user_id' => $userId,
                    'product_id' => $itemId,
                ],
                [
                    'quantity' => 1,
                ]
            );
            $wishlistItem->delete();

            return $this->success(null, 'Item moved to cart successfully');
        }

        return $this->error(null, 'Item not found in wishlist', 404);
    }

    public function getDeals($request): JsonResponse
    {
        $type = $request->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $deals = Deal::with('banners')
            ->select('id', 'title', 'slug', 'image', 'position')
            ->where('type', $type)
            ->latest()
            ->get();

        return $this->success($deals, 'Deals');
    }

    public function getDealDetail($slug): JsonResponse
    {
        $deal = Deal::with('banners')
            ->where('slug', $slug)
            ->first();

        if (! $deal) {
            return $this->error(null, 'Deal not found', 404);
        }

        return $this->success($deal, 'Deal');
    }

    public function flashDeals(): JsonResponse
    {
        $deals = Banner::with('deal')
            ->select('id', 'title', 'slug', 'image', 'start_date', 'end_date', 'deal_id')
            ->whereStatus(BannerStatus::ACTIVE)
            ->get();

        return $this->success($deals, 'Flash deals');
    }

    public function singleFlashDeal($slug): JsonResponse
    {
        $deal = Banner::where('slug', $slug)
            ->whereStatus(BannerStatus::ACTIVE)
            ->first();

        if (! $deal) {
            return $this->error(null, 'Flash deal not found', 404);
        }

        $productIds = $deal->product_ids;
        $products = Product::select(
            'id',
            'name',
            'slug',
            'description',
            'category_id',
            'sub_category_id',
            'price',
            'country_id',
            'image',
        )
            ->whereIn('id', $productIds)
            ->with([
                'category:id,name,slug',
                'subCategory:id,name,slug',
                'shopCountry',
            ])
            ->get();

        $products->each(function ($product): void {
            $product->default_currency = $product->shopCountry->currency ?? 'USD';
            unset($product->shopCountry);
        });

        unset($deal->products, $deal->status, $deal->created_at, $deal->updated_at);

        $data = [
            'deal' => $deal,
            'products' => $products,
        ];

        return $this->success($data, 'Flash deal');
    }

    public function search(\Illuminate\Http\Request $request)
    {
        $countryId = $request->query('country_id');
        $type = $request->query('type');
        $categoryId = $request->query('category_id');

        if (! $countryId && ! $type) {
            return $this->error(null, 'Country & type not selected', 400);
        }

        $search = $request->query('q');

        return match ($type) {
            'product' => $this->searchByProduct($countryId, $search, $categoryId),
            'order' => $this->searchByOrder($search),
            default => $this->error(null, 'Invalid type', 400),
        };
    }
}
