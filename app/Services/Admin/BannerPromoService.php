<?php

namespace App\Services\Admin;

use App\Enum\BannerStatus;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use App\Trait\HttpResponse;

class BannerPromoService
{
    use HttpResponse;

    public function addBanner($request)
    {
        try {

            $image = uploadImage($request, 'image', 'banner');
            
            Banner::create([
                'title' => $request->title,
                'image' => $image,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'products' => $request->products,
                'status' => BannerStatus::ACTIVE,
            ]);

            return $this->success(null, "Added successfully");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function banners()
    {
        $banners = Banner::get();
        $data = BannerResource::collection($banners);

        return $this->success($data, "Banners");
    }

    public function editBanner($request, $id)
    {
        $banner = Banner::findOrFail($id);

        try {

            $image = uploadImage($request, 'image', 'banner', null, $banner);
            
            $banner->update([
                'title' => $request->title,
                'image' => $image,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'products' => $request->products,
            ]);

            return $this->success(null, "Updated successfully");
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return $this->success(null, "Deleted successfully");
    }
}





