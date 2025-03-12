<?php

namespace App\Services\Admin;

use App\Models\Size;
use App\Models\Unit;
use App\Models\User;
use App\Models\Admin;
use App\Models\Brand;
use App\Models\Color;
use App\Models\State;
use App\Models\Country;
use App\Enum\BannerType;
use App\Enum\BannerStatus;
use App\Models\ShopCountry;
use App\Models\SliderImage;
use App\Trait\HttpResponse;
use App\Models\B2bProductCategory;
use Illuminate\Support\Facades\App;
use App\Http\Resources\StateResource;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\SliderResource;
use App\Http\Resources\CountryResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\AdminUserResource;
use App\Http\Resources\B2BCategoryResource;
use App\Http\Resources\ShopCountryResource;

class B2BAdminService
{
    use HttpResponse;

    public function addSlider($data)
    {
        try {

            $folder = null;

            if (App::environment('production')) {
                $folder = '/prod/slider_image';
            } elseif (App::environment(['staging', 'local'])) {
                $folder = '/stag/slider_image';
            }

            if ($data->file('image')) {
                $path = $data->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            SliderImage::create([
                'image' => $url,
                'type' => BannerType::B2B,
                'link' => $data->link
            ]);

            return $this->success(null, "Created successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
    public function updateSlider($id, $data)
    {
        $slider = SliderImage::where('type', BannerType::B2B)->findOrFail($id);
        try {

            $folder = null;

            if (App::environment('production')) {
                $folder = '/prod/slider_image';
            } elseif (App::environment(['staging', 'local'])) {
                $folder = '/stag/slider_image';
            }

            if ($data->file('image')) {
                $path = $data->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            $slider->update([
                'image' => $url ?? $slider->image,
                'link' => $data->link
            ]);

            return $this->success(null, "Details Updated successfully");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
    public function getSlider($id)
    {
        $slider = SliderImage::where('type', BannerType::B2B)->findOrFail($id);
        $data = new SliderResource($slider);
        return $this->success($data, "Slider details");
    }
    public function deleteSlider($id)
    {
        $slider = SliderImage::where('type', BannerType::B2B)->findOrFail($id);
        $slider->delete();
        return $this->success(null, "details deleted");
    }

    public function sliders()
    {
        $sliders = Cache::rememberForever('home_sliders', function () {
            return SliderImage::where('type', BannerType::B2B)->latest('id')->take(5)->get();
        });

        $data = SliderResource::collection($sliders);

        return $this->success($data, "Sliders");
    }

    public function categories()
    {
        $categories = B2bProductCategory::where('featured', 1)
            ->where('status', 'active')
            ->get();

        $data = B2BCategoryResource::collection($categories);

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
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($brands, "All brands");
    }

    public function colors()
    {
        $colors = Color::select('id', 'name', 'code')
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($colors, "All colors");
    }

    public function units()
    {
        $units = Unit::select('id', 'name')
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($units, "All units");
    }

    public function sizes()
    {
        $sizes = Size::select('id', 'name')
            ->where('status', BannerStatus::ACTIVE)
            ->get();

        return $this->success($sizes, "All sizes");
    }

    public function shopByCountry($request)
    {
        $country = Country::find($request->country_id);

        if (! $country) {
            return $this->error(null, "Not found", 404);
        }

        $folder = null;

        if (App::environment('production')) {
            $folder = '/prod/shopcountryflag';
        } elseif (App::environment(['staging', 'local'])) {
            $folder = '/stag/shopcountryflag';
        }

        $url = uploadImage($request, 'flag', $folder);
        ShopCountry::create([
            'country_id' => $country->id,
            'name' => $country->name,
            'flag' => $url,
            'currency' => $request->currency,
        ]);
        return $this->success(null, "Added successfully");
    }

    public function getShopByCountry()
    {
        $shopByCountries = ShopCountry::orderBy('name', 'asc')->get();
        $data = ShopCountryResource::collection($shopByCountries);

        return $this->success($data, "List");
    }

    public function referralGenerate()
    {
        $users = User::whereNull('referrer_code')
            ->orWhere('referrer_code', '')
            ->orWhereNull('referrer_link')
            ->orWhere('referrer_link', '')
            ->get();

        foreach ($users as $user) {
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
