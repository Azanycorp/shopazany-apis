<?php

namespace App\Services\Admin;

use App\Models\Promo;
use App\Models\Banner;
use App\Enum\CouponType;
use App\Models\B2bPromo;
use App\Models\B2bBanner;
use App\Enum\BannerStatus;
use App\Trait\HttpResponse;
use App\Http\Resources\PromoResource;
use App\Http\Resources\BannerResource;
use App\Http\Resources\B2BBannerResource;

class B2BBannerPromoService
{
    use HttpResponse;

    public function addBanner($request)
    {
        try {

            $image = uploadImage($request, 'image', 'banner');

            B2bBanner::create([
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
        $banners = B2bBanner::get();
        if ($banners->isEmpty()) {
            return $this->error(null, "No record found", 404);
        }
        $data = B2BBannerResource::collection($banners);

        return $this->success($data, "Banners");
    }

    public function getOneBanner($id)
    {
        $banners = B2bBanner::findOrFail($id);
        $data = new B2BBannerResource($banners);

        return $this->success($data, "Banner detail");
    }

    public function editBanner($request, $id)
    {
        $banner = B2bBanner::findOrFail($id);

        try {
            if ($request->hasFile('image')) {
                # code...
                $image = uploadImage($request, 'image', 'banner', null, $banner);
            }

            $banner->update([
                'title' => $request->title,
                'image' => $request->image ? $image : $banner->image,
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
        $banner = B2bBanner::findOrFail($id);
        $banner->delete();

        return $this->success(null, "Deleted successfully");
    }

    //Promo section
    public function addPromo($request)
    {
        $type = $request->coupon_type;

        switch ($type) {
            case CouponType::PRODUCT:
                return $this->product($type, $request);
                break;

            case CouponType::TOTAL_ORDERS:
                return $this->totalOrders($type, $request);
                break;

            case CouponType::WELCOME_COUPON:
                return $this->welcomeCoupon($type, $request);
                break;

            default:
                # code...
                break;
        }
    }

    public function promos()
    {
        $promos = B2bPromo::get();
        $data = PromoResource::collection($promos);

        return $this->success($promos, "All Promos");
    }

    public function deletePromo($id)
    {
        $promo = B2bPromo::findOrFail($id);
        $promo->delete();

        return $this->success("Deleted successfully");
    }

    protected function product($type, $request)
    {
        $promo = B2bPromo::create([
            'type' => $type,
            'coupon_code' => $request->coupon_code,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
        ]);

        foreach ($request->products as $product_id) {
            $promo->b2bPromoProduct()->create([
                'product_id' => $product_id
            ]);
        }

        return $this->success($promo, 'Created successfully');
    }

    protected function totalOrders($type, $request)
    {
        $promo = B2bPromo::create([
            'type' => $type,
            'coupon_code' => $request->coupon_code,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
        ]);

        $promo->b2bTotalOrder()->create([
            'minimum_cart_amount' => $request->minimum_cart_amount,
            'maximum_discount_amount' => $request->maximum_discount_amount,
        ]);

        return $this->success($promo, 'Created successfully');
    }

    protected function welcomeCoupon($type, $request)
    {
        $promo = B2bPromo::create([
            'type' => $type,
            'coupon_code' => $request->coupon_code,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
        ]);

        $promo->b2bWelcomeCoupon()->create([
            'minimum_shopping_amount' => $request->minimum_shopping_amount,
            'number_of_days_valid' => $request->number_of_days_valid,
        ]);

        return $this->success($promo, 'Created successfully');
    }
}
