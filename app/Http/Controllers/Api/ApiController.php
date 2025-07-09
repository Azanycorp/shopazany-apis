<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminService;
use App\Services\User\CustomerService;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function __construct(
        protected AdminService $adminService,
        protected CustomerService $customerService
    ) {}

    public function addSlider(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:png,jpg,jpeg'],
        ]);

        return $this->adminService->addSlider($request);
    }

    public function slider()
    {
        return $this->adminService->slider();
    }

    public function categories()
    {
        return $this->adminService->categories();
    }

    public function country()
    {
        return $this->adminService->country();
    }

    public function states($id)
    {
        return $this->adminService->states($id);
    }

    public function brands()
    {
        return $this->adminService->brands();
    }

    public function colors()
    {
        return $this->adminService->colors();
    }

    public function units()
    {
        return $this->adminService->units();
    }

    public function sizes()
    {
        return $this->adminService->sizes();
    }

    public function shopByCountry(Request $request)
    {
        $request->validate([
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'flag' => ['required', 'mimes:png,jpg,jpeg,svg'],
        ]);

        return $this->adminService->shopByCountry($request);
    }

    public function getShopByCountry(): array
    {
        return $this->adminService->getShopByCountry();
    }

    public function userShopByCountry($countryId)
    {
        return $this->customerService->userShopByCountry($countryId);
    }

    public function referralGenerate()
    {
        return $this->adminService->referralGenerate();
    }

    public function adminProfile()
    {
        return $this->adminService->adminProfile();
    }
}
