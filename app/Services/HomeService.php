<?php

namespace App\Services;

use App\Http\Resources\SellerProductResource;
use App\Http\Resources\SingleProductResource;
use App\Models\Product;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;

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
            ->where('orders.status', 'delivered')
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
}

