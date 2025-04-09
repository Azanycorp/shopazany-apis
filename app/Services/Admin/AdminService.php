<?php

namespace App\Services\Admin;

use App\Enum\CategoryStatus;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ShopCountryResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\StateResource;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Country;
use App\Models\ShopCountry;
use App\Models\Size;
use App\Models\SliderImage;
use App\Models\State;
use App\Models\Unit;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AdminService
{
    use HttpResponse;

    public function addSlider($request)
    {
        try {
            $folder = App::environment('production') ? '/prod/slider_image' : '/stag/slider_image';

            if ($request->file('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            SliderImage::create([
                'image' => $url,
                'link' => $request->link
            ]);

            return $this->success(null, "Created successfully", 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function slider()
    {
        $sliders = Cache::rememberForever('home_sliders', function () {
            return SliderImage::orderBy('created_at', 'desc')->take(5)->get();
        });

        $data = SliderResource::collection($sliders);

        return $this->success($data, "Sliders");
    }

    public function categories()
    {
        $categories = Category::where('featured', 1)
            ->whereStatus(CategoryStatus::ACTIVE)
            ->get();

        $data = CategoryResource::collection($categories);

        return $this->success($data, "Categories");
    }

    public function country()
    {
        $country = Cache::rememberForever('country', function () {
            return Country::get();
        });

        $data = CountryResource::collection($country);

        return $this->success($data, "All Country");
    }

    public function states($id)
    {
        $states = State::where('country_id', $id)->get();

        $data = StateResource::collection($states);

        return $this->success($data, "States");
    }

    public function brands()
    {
        $brands = Brand::select('id', 'name', 'slug', 'image')
            ->whereStatus('active')
            ->get();

        return $this->success($brands, "All brands");
    }

    public function colors()
    {
        $colors = Color::select('id', 'name', 'code')
            ->whereStatus('active')
            ->get();

        return $this->success($colors, "All colors");
    }

    public function units()
    {
        $units = Unit::select('id', 'name')
            ->whereStatus('active')
            ->get();

        return $this->success($units, "All units");
    }

    public function sizes()
    {
        $sizes = Size::select('id', 'name')
            ->whereStatus('active')
            ->get();

        return $this->success($sizes, "All sizes");
    }

    public function shopByCountry($request)
    {
        $country = Country::find($request->country_id);

        if(!$country) {
            return $this->error(null, "Not found", 404);
        }

        $folder = App::environment('production') ? '/prod/shopcountryflag' : '/stag/shopcountryflag';

        $url = uploadImage($request, 'flag', $folder);

        ShopCountry::create([
            'country_id' => $country->id,
            'name' => $country->name,
            'flag' => $url,
            'currency' => $request->currency,
        ]);

        return $this->success(null, "Added successfully", 201);
    }

    public function getShopByCountry(): array
    {
        $priorityCountries = ['Jamaica', 'Switzerland', 'Brazil', 'France', 'Nigeria', 'United Kingdom', 'Canada', 'United States'];

        $shopByCountries = ShopCountry::orderByRaw("FIELD(name, '".implode("','", $priorityCountries)."') DESC")
            ->orderBy('name', 'asc')
            ->get();

        $data = ShopCountryResource::collection($shopByCountries);
        $totalCount = $shopByCountries->count();

        return [
            'status' => true,
            'message' => 'Shop By Country List',
            'data' => $data,
            'total_count' => $totalCount,
        ];
    }

    public function referralGenerate()
    {
        $users = User::whereNull('referrer_code')
            ->orWhere('referrer_code', '')
            ->orWhereNull('referrer_link')
            ->orWhere('referrer_link', '')
            ->get();

        foreach($users as $user) {
            if (!$user->referrer_code) {
                $user->referrer_code = generate_referral_code();
            }

            if (!$user->referral_link) {
                $user->referrer_link = generate_referrer_link($user->referrer_code);
            }

            $user->save();
        }

        return $this->success(null, "Generated successfully");
    }

    public function adminProfile()
    {
        $authUser = userAuth();
        $user = Admin::findOrFail($authUser->id);
        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }
}

