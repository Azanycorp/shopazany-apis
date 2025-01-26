<?php

namespace App\Services\Admin;

use App\Enum\BannerStatus;
use App\Enum\CouponType;
use App\Http\Resources\BannerResource;
use App\Http\Resources\PromoResource;
use App\Models\Banner;
use App\Models\Promo;
use App\Trait\HttpResponse;

class BannerPromoService
{
    use HttpResponse;

    public function addBanner($request)
    {
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
    }

    public function banners()
    {
        $banners = Banner::get();
        $data = BannerResource::collection($banners);

        return $this->success($data, "Banners");
    }

    public function getOneBanner($id)
    {
        $banners = Banner::findOrFail($id);
        $data = new BannerResource($banners);

        return $this->success($data, "Banner detail");
    }

    public function editBanner($request, $id)
    {
        $banner = Banner::findOrFail($id);
        $image = uploadImage($request, 'image', 'banner', null, $banner);
        $banner->update([
            'title' => $request->title,
            'image' => $image,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'products' => $request->products,
        ]);
        return $this->success(null, "Updated successfully");
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return $this->success(null, "Deleted successfully");
    }

    public function addPromo($request)
    {
        $type = $request->coupon_type;

        switch ($type) {
            case CouponType::PRODUCT:
                return $this->product($type, $request);

            case CouponType::TOTAL_ORDERS:
                return $this->totalOrders($type, $request);

            case CouponType::WELCOME_COUPON:
                return $this->welcomeCoupon($type, $request);

            default:
                # code...
                break;
        }
        return null;
    }

    public function promos()
    {
        $promos = Promo::get();
        $data = PromoResource::collection($promos);

        return $this->success($data, "All Promos");
    }

    public function deletePromo($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return $this->success(null, "Deleted successfully");
    }

    protected function product($type, $request)
    {
        $promo = Promo::create([
            'type' => $type,
            'coupon_code' => $request->coupon_code,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
        ]);

        foreach ($request->product as $product_id) {
            $promo->promoProduct()->create([
                'product_id' => $product_id
            ]);
        }

        return $this->success(null, 'Created successfully');
    }

    protected function totalOrders($type, $request)
    {
        $promo = Promo::create([
            'type' => $type,
            'coupon_code' => $request->coupon_code,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
        ]);

        $promo->totalOrder()->create([
            'minimum_cart_amount' => $request->minimum_cart_amount,
            'maximum_discount_amount' => $request->maximum_discount_amount,
        ]);

        return $this->success(null, 'Created successfully');
    }

    protected function welcomeCoupon($type, $request)
    {
        $promo = Promo::create([
            'type' => $type,
            'coupon_code' => $request->coupon_code,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
        ]);

        $promo->welcomeCoupon()->create([
            'minimum_shopping_amount' => $request->minimum_shopping_amount,
            'number_of_days_valid' => $request->number_of_days_valid,
        ]);

        return $this->success(null, 'Created successfully');
    }
}





