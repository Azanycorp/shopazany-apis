<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddBannerRequest;
use App\Http\Requests\Admin\AddPromoRequest;
use App\Services\Admin\BannerPromoService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BannerPromoController extends Controller
{
    public const MESSAGE = '403 Forbidden';

    public function __construct(
        private readonly BannerPromoService $service,
        private readonly Gate $gate,
    ) {}

    public function addBanner(AddBannerRequest $request)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addBanner($request);
    }

    public function banners(Request $request)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->banners($request);
    }

    public function getOneBanner($id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getOneBanner($id);
    }

    public function editBanner(Request $request, $id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->editBanner($request, $id);
    }

    public function deleteBanner($id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deleteBanner($id);
    }

    public function addPromo(AddPromoRequest $request)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addPromo($request);
    }

    public function promos()
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->promos();
    }

    public function deletePromo($id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deletePromo($id);
    }

    public function addDeal(Request $request)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        $request->validate([
            'title' => ['required', 'string'],
            'image' => ['required', 'image|mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'position' => ['required', 'string', 'in:top,bottom'],
            'type' => ['required', 'string', 'in:b2c,b2b,agriecom_b2c'],
        ], [
            'type.in' => 'Invalid type',
        ]);

        return $this->service->addDeal($request);
    }

    public function deals(Request $request)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deals($request);
    }

    public function getOneDeal($id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getOneDeal($id);
    }

    public function editDeal(Request $request, $id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->editDeal($request, $id);
    }

    public function deleteDeal($id)
    {
        abort_if($this->gate->denies('banner_promo'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->deleteDeal($id);
    }
}
