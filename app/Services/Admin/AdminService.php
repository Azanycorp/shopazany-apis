<?php

namespace App\Services\Admin;

use App\Http\Resources\SliderResource;
use App\Models\SliderImage;
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
}

