<?php

namespace App\Services\Admin;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\SliderResource;
use App\Http\Resources\StateResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Country;
use App\Models\Size;
use App\Models\SliderImage;
use App\Models\State;
use App\Models\Unit;
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

            $folder = null;

            if(App::environment('production')){
                $folder = '/prod/slider_image';
            } elseif(App::environment(['staging', 'local'])) {
                $folder = '/stag/slider_image';
            }

            if ($request->file('image')) {
                $path = $request->file('image')->store($folder, 's3');
                $url = Storage::disk('s3')->url($path);
            }

            SliderImage::create([
                'image' => $url,
                'link' => $request->link
            ]);

            return $this->success(null, "Created successfully");
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
        ->where('status', 'active')
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
        $brands = Brand::where('status', 'active')->get(['id', 'name', 'slug', 'image']);

        return $this->success($brands, "All brands");
    }

    public function colors()
    {
        $colors = Color::where('status', 'active')->get(['id', 'name', 'code']);

        return $this->success($colors, "All colors");
    }

    public function units()
    {
        $units = Unit::where('status', 'active')->get(['id', 'name']);

        return $this->success($units, "All units");
    }

    public function sizes()
    {
        $sizes = Size::where('status', 'active')->get(['id', 'name']);

        return $this->success($sizes, "All sizes");
    }
}

