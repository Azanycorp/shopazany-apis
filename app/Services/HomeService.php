<?php

namespace App\Services;

use App\Enum\OrderStatus;
use App\Enum\ProductStatus;
use App\Models\User;
use App\Models\Product;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SellerProductResource;
use App\Http\Resources\SingleProductResource;
use App\Models\Brand;
use App\Models\Category;

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

        $query = Product::where('is_featured', true);

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
              ->orderBy('price', 'asc')
              ->limit(4);

        $products = $query->get();

        $data = SellerProductResource::collection($products);

        return $this->success($data, "Pocket friendly products");
    }

    public function productSlug($slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

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
        $category = Category::with('products')
        ->where('slug', $slug)
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
}

