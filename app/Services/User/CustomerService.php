<?php

namespace App\Services\User;

use App\Http\Resources\SellerProductResource;
use App\Models\Country;
use App\Models\Product;
use App\Models\User;
use App\Trait\HttpResponse;

class CustomerService
{
    use HttpResponse;

    public function dashboardAnalytics($userId)
    {
        $currentUser = auth()->user();

        if ($currentUser->id != $userId && $currentUser->type != 'customer') {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('userOrders')->find($userId);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $total_order = $user->userOrders->count();

        $data = [
            'total_order' => $total_order,
        ];

        return $this->success($data, "Dashboard analytics");
    }

    public function userShopByCountry($countryId)
    {
        $country = Country::where('id', $countryId)->first();

        if(!$country) {
            return $this->error(null, "Country not found", 404);
        }

        $products = Product::where('country_id', $country->id)->get();

        $data = SellerProductResource::collection($products);
        
        return $this->success($data, "You are now shopping in {$country->name}");
    }
}


