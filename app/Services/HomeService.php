<?php

namespace App\Services;

use App\Models\Product;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;

class HomeService
{
    use HttpResponse;

    public function bestSelling()
    {
        $countryId = request()->query('country_id');

        $query = Product::with('orders')
                        ->select('products.id', DB::raw('COUNT(orders.id) as total_orders'))
                        ->join('orders', 'orders.product_id', '=', 'products.id')
                        ->where('orders.status', 'completed')
                        ->groupBy('products.id')
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

        return $this->success($featuredProducts, "Featured products");
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

        return $this->success($products, "Pocket friendly products");
    }
}

