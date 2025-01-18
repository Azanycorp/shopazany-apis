<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddBannerRequest;
use App\Http\Requests\Admin\AddPromoRequest;
use App\Services\Admin\BannerPromoService;
use Illuminate\Http\Request;

class BannerPromoController extends Controller
{
    protected $service;

    public function __construct(BannerPromoService $service)
    {
        $this->service = $service;
    }

    public function addBanner(AddBannerRequest $request)
    {
        return $this->service->addBanner($request);
    }

    public function banners()
    {
        return $this->service->banners();
    }

    public function getOneBanner($id)
    {
        return $this->service->getOneBanner($id);
    }

    public function editBanner(Request $request, $id)
    {
        return $this->service->editBanner($request, $id);
    }

    public function deleteBanner($id)
    {
        return $this->service->deleteBanner($id);
    }

    public function addPromo(AddPromoRequest $request)
    {
        return $this->service->addPromo($request);
    }

    public function promos()
    {
        return $this->service->promos();
    }

    public function deletePromo($id)
    {
        return $this->service->deletePromo($id);
    }

}
