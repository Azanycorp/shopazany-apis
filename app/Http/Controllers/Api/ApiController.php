<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use App\Services\User\CustomerService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected \App\Services\Admin\AdminService $service;
    protected \App\Services\User\CustomerService $customerService;

    public function __construct(AdminService $adminService, CustomerService $customerService)
    {
        $this->service = $adminService;
        $this->customerService = $customerService;
    }

    public function addSlider(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg']
        ]);

        return $this->service->addSlider($request);
    }

    public function slider()
    {
        return $this->service->slider();
    }

    public function categories()
    {
        return $this->service->categories();
    }

    public function country()
    {
        return $this->service->country();
    }

    public function states($id)
    {
        return $this->service->states($id);
    }

    public function brands()
    {
        return $this->service->brands();
    }

    public function colors()
    {
        return $this->service->colors();
    }

    public function units()
    {
        return $this->service->units();
    }

    public function sizes()
    {
        return $this->service->sizes();
    }

    public function shopByCountry(Request $request)
    {
        $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'flag' => ['required', 'mimes:png,jpg,jpeg,svg']
        ]);

        return $this->service->shopByCountry($request);
    }

    public function getShopByCountry()
    {
        return $this->service->getShopByCountry();
    }

    public function userShopByCountry($countryId)
    {
        return $this->customerService->userShopByCountry($countryId);
    }

    public function referralGenerate()
    {
        return $this->service->referralGenerate();
    }

    public function adminProfile()
    {
        return $this->service->adminProfile();
    }
}
