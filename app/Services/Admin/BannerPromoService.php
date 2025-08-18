<?php

namespace App\Services\Admin;

use App\Enum\BannerStatus;
use App\Enum\CouponType;
use App\Http\Resources\BannerResource;
use App\Http\Resources\PromoResource;
use App\Models\Banner;
use App\Models\Deal;
use App\Models\Promo;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;

class BannerPromoService
{
    use HttpResponse;

    public function addBanner($request)
    {
        $image = uploadImage($request, 'image', 'banner');

        $products = $request->products;

        $prods = array_map(function ($seat): int {
            return (int) trim($seat, '"');
        }, $products);

        $slug = Str::slug($request->title);
        if (Banner::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        Banner::create([
            'title' => $request->title,
            'slug' => $slug,
            'image' => $image['url'],
            'public_id' => $image['public_id'],
            'deal_id' => $request->deal_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'products' => $prods,
            'status' => BannerStatus::ACTIVE,
        ]);

        return $this->success(null, 'Added successfully', 201);
    }

    public function banners()
    {
        $banners = Banner::with('deal')->get();
        $data = BannerResource::collection($banners);

        return $this->success($data, 'Banners');
    }

    public function getOneBanner($id)
    {
        $banner = Banner::with('deal')->findOrFail($id);
        $data = new BannerResource($banner);

        return $this->success($data, 'Banner detail');
    }

    public function editBanner($request, $id)
    {
        $banner = Banner::findOrFail($id);
        $image = uploadImage($request, 'image', 'banner', null, $banner);

        $slug = Str::slug($request->title);
        if (Banner::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        $banner->update([
            'title' => $request->title,
            'slug' => $slug,
            'image' => $request->image ? $image['url'] : $banner->image,
            'deal_id' => $request->deal_id,
            'public_id' => $request->image ? $image['public_id'] : $banner->public_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'products' => $request->products,
        ]);

        return $this->success(null, 'Updated successfully');
    }

    public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->delete();

        return $this->success(null, 'Deleted successfully');
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
                // code...
                break;
        }

        return null;
    }

    public function promos()
    {
        $promos = Promo::get();
        $data = PromoResource::collection($promos);

        return $this->success($data, 'All Promos');
    }

    public function deletePromo($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->delete();

        return $this->success(null, 'Deleted successfully');
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
                'product_id' => $product_id,
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

    public function addDeal($request)
    {
        if ($request->hasFile('image')) {
            $image = uploadFunction($request->file('image'), 'deals');
        }

        $slug = Str::slug($request->title);
        if (Deal::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        $deal = Deal::query()->create([
            'title' => $request->title,
            'slug' => $slug,
            'image' => $image['url'],
            'public_id' => $image['public_id'],
            'position' => $request->position,
        ]);

        return $this->success($deal, 'Deal added successfully');
    }

    public function deals()
    {
        $deals = Deal::select('id', 'title', 'slug', 'image', 'position')
            ->latest()
            ->get();

        return $this->success($deals, 'Deals');
    }

    public function getOneDeal($id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return $this->error(null, 'Deal not found', 404);
        }

        return $this->success($deal, 'Deal');
    }

    public function editDeal($request, $id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return $this->error(null, 'Deal not found', 404);
        }

        if ($request->hasFile('image')) {
            $image = uploadFunction($request->file('image'), 'deals', $deal);
        }

        $slug = Str::slug($request->title);
        if (Deal::where('slug', $slug)->exists()) {
            $slug = $slug.'-'.uniqid();
        }

        $deal->update([
            'title' => $request->title ?? $deal->title,
            'slug' => $slug,
            'image' => $image['url'] ?? $deal->image,
            'public_id' => $image['public_id'] ?? $deal->public_id,
            'position' => $request->position ?? $deal->position,
        ]);

        return $this->success($deal, 'Deal updated successfully');
    }

    public function deleteDeal($id)
    {
        $deal = Deal::find($id);

        if (!$deal) {
            return $this->error(null, 'Deal not found', 404);
        }

        $deal->delete();

        return $this->success(null, 'Deal deleted successfully');
    }
}
