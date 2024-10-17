<?php

namespace App\Services;

use App\Enum\OrderStatus;
use App\Enum\ProductReviewStatus;
use App\Enum\ProductStatus;
use App\Enum\UserType;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\SellerDetailResource;
use App\Models\User;
use App\Models\Product;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SellerProductResource;
use App\Http\Resources\SingleProductResource;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class HomeService
{
    use HttpResponse;

    public function bestSelling()
    {
        $countryId = request()->query('country_id');

        $query = Product::select(
            'products.id',
            'products.name',
            'products.slug',
            'products.image',
            'products.price',
            'products.description',
            'products.category_id',
            DB::raw('COUNT(orders.id) as total_orders'))
            ->leftJoin('orders', 'orders.product_id', '=', 'products.id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->groupBy('products.id', 'products.name', 'products.price', 'products.slug', 'products.image', 'products.description',
            'products.category_id')
            ->orderBy('total_orders', 'DESC')
            ->take(10);

        if ($countryId) {
            $query->where('orders.country_id', $countryId);
        }

        $products = $query->get();

        return $this->success($products, "Best selling products");
    }


    public function featuredProduct()
    {
        $countryId = request()->query('country_id');

        $query = Product::where('is_featured', true)
            ->where('status', ProductStatus::ACTIVE);

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $featuredProducts = $query->limit(8)->get();

        $data = SellerProductResource::collection($featuredProducts);

        return $this->success($data, "Featured products");
    }

    public function pocketFriendly()
    {
        $countryId = request()->query('country_id');

        $query = Product::query();

        if ($countryId) {
            $query->where('country_id', $countryId);
        }

        $query->whereBetween('price', [1000, 10000])
            ->where('status', ProductStatus::ACTIVE)
            ->orderBy('price', 'asc')
            ->limit(4);

        $products = $query->get();

        $data = SellerProductResource::collection($products);

        return $this->success($data, "Pocket friendly products");
    }

    public function productSlug($slug)
    {
        $product = Product::with(['brand', 'category', 'subCategory', 'color', 'unit', 'size', 'productReviews'])
        ->withCount('productReviews')
        ->where('slug', $slug)
        ->firstOrFail();

        $data = new SingleProductResource($product);

        return $this->success($data, "Product detail");
    }

    public function topBrands()
    {
        $brands = Brand::select(['id', 'name', 'slug', 'image'])
        ->where('status', 'active')
        ->latest()
        ->take(8)
        ->get();

        return $this->success($brands, "Top brands");
    }

    public function topSellers()
    {
        $countryId = request()->input('country_id');

        $topSellersQuery = User::select(DB::raw('users.id as user_id, CONCAT(users.first_name, " ", users.last_name) as name, users.image as image, COUNT(orders.id) as total_sales'))
            ->join('orders', 'users.id', '=', 'orders.seller_id')
            ->where('orders.status', OrderStatus::DELIVERED)
            ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.image')
            ->orderByDesc('total_sales');

        if ($countryId) {
            $topSellersQuery->where('orders.country_id', $countryId);
        } else {
            $topSellersQuery->limit(8);
        }

        $topSellers = $topSellersQuery->get();

        return $this->success($topSellers, "Top sellers");
    }

    public function categorySlug($slug)
    {
        $category = Category::with(['products' => function ($query) {
            $query->where('status', ProductStatus::ACTIVE);
        }])->where('slug', $slug)
        ->firstOrFail();

        $products = SellerProductResource::collection($category->products);

        return $this->success($products, 'Products by category');
    }

    public function recommendedProducts()
    {
        $products = Product::where('status', ProductStatus::ACTIVE)
        ->select(['id', 'name', 'slug', 'description', 'discount_price', 'price', 'image'])
        ->take(50)
        ->get()
        ->shuffle()
        ->take(6);

        return $this->success($products, "Recommended products");
    }

    public function productReview($request)
    {
        $product = Product::with('productReviews')
        ->findOrFail($request->product_id);

        $product->productReviews()->create([
            'user_id' => $request->user_id,
            'rating' => $request->rating,
            'review' => $request->review,
            'status' => ProductReviewStatus::APPROVED,
        ]);

        return $this->success(null, "Review added successfully");
    }

    public function saveForLater($request)
    {
        $currentUser = userAuth();

        if ($currentUser->id != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::findOrFail($request->user_id);
        $product = Product::findOrFail($request->product_id);

        if ($user->wishlist()->where('product_id', $product->id)->exists()) {
            return $this->error(null, "Product already in wishlist", 409);
        }

        $user->wishlist()->create([
            'product_id' => $product->id,
        ]);

        return $this->success(null, "Product saved for later");
    }

    public function sellerInfo($uuid)
    {
        $search = request()->input('search');

        $user = User::with([
            'products' => function ($query) use ($search) {
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                          ->orWhere('description', 'like', '%' . $search . '%');
                    });
                }
                $query->with(['category', 'subCategory']);
                $query->withCount(['productReviews']);
                $query->withCount(['orders as item_sold' => function ($query) {
                    $query->where('status', OrderStatus::DELIVERED);
                }]);
            }
        ])->where('uuid', $uuid)
        ->withCount('products')
        ->first();

        if(! $user) {
            return $this->error(null, 'Not found', 400);
        }

        $data = new SellerDetailResource($user);

        return $this->success($data, 'Seller details');
    }

    public function sellerCategory($uuid)
    {
        $search = request()->input('search');

        $user = User::where('uuid', $uuid)
            ->with([
                'products' => function ($query) use ($search) {
                    $query->with(['category' => function ($q) use($search) {
                        $q->select(['id', 'name', 'slug', 'image']);

                        if ($search) {
                            $q->where('name', 'like', '%' . $search . '%');
                        }
                    }]);
                }
            ])
            ->first();

        if (!$user) {
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

    public function sellerReviews($uuid)
    {
        $search = request()->input('search');
        $perPage = request()->input('per_page', 4);
        $currentPage = request()->input('page', 1);

        $user = User::where('uuid', $uuid)
            ->with(['products' => function ($query) use ($search) {
                $query->with(['productReviews' => function ($q) use ($search) {
                    if ($search) {
                        $q->where('review', 'like', '%' . $search . '%');
                    }

                    $q->with(['user' => function ($userQuery) {
                        $userQuery->select('id', 'first_name', 'last_name');
                    }]);

                    $q->select('id', 'user_id', 'product_id', 'rating', 'review', 'created_at');
                }]);
            }])
            ->first();

        if (!$user) {
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
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $reviewResources = ReviewResource::collection($paginatedReviews);

        $responseData = [
            'reviews' => $reviewResources,
            'overall_rating' => (float)number_format($overallRating, 1),
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


}

