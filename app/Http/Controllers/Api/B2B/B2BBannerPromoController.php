<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\B2BAdminService;
use App\Services\Admin\BannerPromoService;
use App\Http\Requests\Admin\AddPromoRequest;
use App\Http\Requests\Admin\AddBannerRequest;
use App\Http\Requests\Admin\NewSliderRequest;
use App\Services\Admin\B2BBannerPromoService;
use App\Http\Requests\Admin\B2BAddPromoRequest;

class B2BBannerPromoController extends Controller
{
    public function __construct(
        private B2BBannerPromoService $service,
        private B2BAdminService $b2BAdminService
    ) {}

    //sliders
    public function sliders()
    {
        return $this->b2BAdminService->sliders();
    }

    public function addSlider(NewSliderRequest $request)
    {
        return $this->b2BAdminService->addSlider($request);
    }
    public function getSlider($id)
    {
        return $this->b2BAdminService->getSlider($id);
    }
    public function updateSlider(NewSliderRequest $request, $id)
    {
        return $this->b2BAdminService->updateSlider($request, $id);
    }
    public function deleteSlider($id)
    {
        return $this->b2BAdminService->deleteSlider($id);
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

    public function addPromo(B2BAddPromoRequest $request)
    {
        return $this->service->addPromo($request);
    }

    public function promos()
    {
        return $this->service->promos();
    }
    public function getProducts()
    {
        return $this->service->getProducts();
    }

    public function deletePromo($id)
    {
        return $this->service->deletePromo($id);
    }
}
