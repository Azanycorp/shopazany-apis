<?php

namespace App\Services\Admin;

use App\Enum\BannerStatus;
use App\Enum\BannerType;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\B2BCategoryResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\ShopCountryResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\StateResource;
use App\Models\Admin;
use App\Models\B2bProductCategory;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Country;
use App\Models\ShopCountry;
use App\Models\Size;
use App\Models\SliderImage;
use App\Models\State;
use App\Models\Unit;
use App\Models\User;
use App\Trait\HttpResponse;

class B2BAdminService
{
    use HttpResponse;

    public function __construct(private readonly \Illuminate\Contracts\Cache\Repository $cacheManager) {}

    public function addSlider($request)
    {
        try {
            if ($request->file('image')) {
                $url = uploadFunction($request->file('image'), 'slider_image');
            }

            SliderImage::create([
                'image' => $url['url'] ?? null,
                'public_id' => $url['public_id'] ?? null,
                'type' => BannerType::B2B,
                'link' => $request->link,
            ]);

            return $this->success(null, 'Created successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function updateSlider($request, $id)
    {
        $slider = SliderImage::where('type', BannerType::B2B)->findOrFail($id);
        try {

            if ($request->file('image')) {
                $url = uploadFunction($request->file('image'), 'slider_image', $slider);
            }

            $slider->update([
                'image' => $url['url'] ?? $slider->image,
                'public_id' => $url['public_id'] ?? $slider->public_id,
                'link' => $request->link,
            ]);

            return $this->success(null, 'Details Updated successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function getSlider($id)
    {
        $slider = SliderImage::where('type', BannerType::B2B)->findOrFail($id);
        $data = new SliderResource($slider);

        return $this->success($data, 'Slider details');
    }

    public function deleteSlider($id)
    {
        $slider = SliderImage::where('type', BannerType::B2B)->findOrFail($id);
        $slider->delete();

        return $this->success(null, 'details deleted');
    }

    public function sliders()
    {
        $sliders = SliderImage::where('type', BannerType::B2B)->latest()->take(5)->get();
        $data = SliderResource::collection($sliders);

        return $this->success($data, 'Sliders');
    }

    public function categories()
    {
        $categories = B2bProductCategory::where('featured', 1)
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        $data = B2BCategoryResource::collection($categories);

        return $this->success($data, 'Categories');
    }

    public function country()
    {
        $country = $this->cacheManager->rememberForever('country', function () {
            return Country::get();
        });

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
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($brands, 'All brands');
    }

    public function colors()
    {
        $colors = Color::select('id', 'name', 'code')
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($colors, 'All colors');
    }

    public function units()
    {
        $units = Unit::select('id', 'name')
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($units, 'All units');
    }

    public function sizes()
    {
        $sizes = Size::select('id', 'name')
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($sizes, 'All sizes');
    }

    public function shopByCountry($request)
    {
        $country = Country::find($request->country_id);

        if (! $country) {
            return $this->error(null, 'Not found', 404);
        }

        if ($request->file('flag')) {
            $url = uploadImage($request, 'flag', 'shopcountryflag');
        }

        ShopCountry::create([
            'country_id' => $country->id,
            'name' => $country->name,
            'flag' => $url['url'],
            'currency' => $request->currency,
        ]);

        return $this->success(null, 'Added successfully');
    }

    public function getShopByCountry()
    {
        $shopByCountries = ShopCountry::orderBy('name', 'asc')->get();
        $data = ShopCountryResource::collection($shopByCountries);

        return $this->success($data, 'List');
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
