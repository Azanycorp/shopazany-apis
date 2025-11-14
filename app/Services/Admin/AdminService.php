<?php

namespace App\Services\Admin;

use App\Enum\BannerType;
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
use Illuminate\Support\Facades\Cache;

class AdminService
{
    use HttpResponse;

    public function addSlider($request)
    {
        try {
            if ($request->file('image')) {
                $url = uploadFunction($request->file('image'), 'slider_image');
            }

            SliderImage::create([
                'image' => $url['url'] ?? null,
                'public_id' => $url['public_id'] ?? null,
                'link' => $request->link,
            ]);

            if (Cache::has('home_sliders')) {
                Cache::forget('home_sliders');
            }

            return $this->success(null, 'Created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 400);
        }
    }

    public function slider()
    {
        $sliders = Cache::rememberForever('home_sliders',
            fn () => SliderImage::orderBy('created_at', 'desc')
                ->take(5)
                ->get()
        );

        $data = SliderResource::collection($sliders);

        return $this->success($data, 'Sliders');
    }

    public function getOneSlider($id)
    {
        $slider = SliderImage::find($id);

        if (! $slider) {
            return $this->error(null, 'Slider not found', 404);
        }

        $data = new SliderResource($slider);

        return $this->success($data, 'Slider detail');
    }

    public function deleteSlider($id)
    {
        $slider = SliderImage::find($id);

        if (! $slider) {
            return $this->error(null, 'Slider not found', 404);
        }

        if (Cache::has('home_sliders')) {
            Cache::forget('home_sliders');
        }

        $slider->delete();

        return $this->success(null, 'Deleted successfully');
    }

    public function categories()
    {
        $type = request()->query('type', BannerType::B2C);

        if (! in_array($type, [BannerType::B2C, BannerType::B2B, BannerType::AGRIECOM_B2C])) {
            return $this->error(null, "Invalid type {$type}", 400);
        }

        $categories = Category::where('featured', true)
            ->where('type', $type)
            ->whereStatus(CategoryStatus::ACTIVE)
            ->get();

        $data = CategoryResource::collection($categories);

        return $this->success($data, 'Categories');
    }

    public function country()
    {
        $country = Cache::rememberForever('country', fn () => Country::get());

        $data = CountryResource::collection($country);

        return $this->success($data, 'All Country');
    }

    public function states($id)
    {
        $states = State::where('country_id', $id)->get();

        $data = StateResource::collection($states);

        return $this->success($data, 'States');
    }

    public function brands()
    {
        $brands = Brand::select('id', 'name', 'slug', 'image')
            ->whereStatus('active')
            ->get();

        return $this->success($brands, 'All brands');
    }

    public function colors()
    {
        $colors = Color::select('id', 'name', 'code')
            ->whereStatus('active')
            ->get();

        return $this->success($colors, 'All colors');
    }

    public function units()
    {
        $units = Unit::select('id', 'name')
            ->whereStatus('active')
            ->get();

        return $this->success($units, 'All units');
    }

    public function sizes()
    {
        $sizes = Size::select('id', 'name')
            ->whereStatus('active')
            ->get();

        return $this->success($sizes, 'All sizes');
    }

    public function shopByCountry($request)
    {
        $country = Country::find($request->country_id);

        if (! $country) {
            return $this->error(null, 'Not found', 404);
        }

        $url = uploadImage($request, 'flag', 'shopcountryflag');

        ShopCountry::create([
            'country_id' => $country->id,
            'name' => $country->name,
            'flag' => $url['url'],
            'currency' => $request->currency,
        ]);

        return $this->success(null, 'Added successfully', 201);
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

        foreach ($users as $user) {
            if (! $user->referrer_code) {
                $user->referrer_code = generate_referral_code();
            }

            if (! $user->referral_link) {
                $user->referrer_link = generate_referrer_link($user->referrer_code);
            }

            $user->save();
        }

        return $this->success(null, 'Generated successfully');
    }

    public function adminProfile()
    {
        $authUser = userAuth();
        $user = Admin::findOrFail($authUser->id);
        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }
}
