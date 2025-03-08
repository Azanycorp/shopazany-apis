<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Requests\LoginRequest;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Services\B2B\Auth\AuthService;
use App\Http\Requests\B2B\SignupRequest;
use App\Http\Requests\B2B\BuyerOnboardingRequest;
use App\Http\Requests\B2B\BusinessInformationRequest;

class B2BController extends Controller
{
    protected \App\Services\B2B\Auth\AuthService $service;
    protected \App\Services\B2B\SellerService $sellerService;
    protected \App\Services\B2B\BuyerService $buyerService;

    public function __construct(AuthService $service, SellerService $sellerService, BuyerService $buyerService)
    {
        $this->service = $service;
        $this->sellerService = $sellerService;
        $this->buyerService = $buyerService;
    }

    public function login(LoginRequest $request)
    {
        return $this->service->login($request);
    }

    public function signup(SignupRequest $request)
    {
        return $this->service->signup($request);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code' => 'required|string',
        ]);

        return $this->service->verify($request);
    }

    public function resendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        return $this->service->resendCode($request);
    }

    public function businessInformation(BusinessInformationRequest $request)
    {
        return $this->sellerService->businessInformation($request);
    }

    public function buyerOnboarding(BuyerOnboardingRequest $request)
    {
        return $this->service->buyerOnboarding($request);
    }

    public function searchProduct()
    {
        return $this->buyerService->searchProduct();
    }
    public function getCategoryProducts()
    {
        return $this->buyerService->getCategoryProducts();
    }
    public function bestSellingProduct()
    {
        return $this->buyerService->bestSelling();
    }
    public function featuredProduct()
    {
        return $this->buyerService->featuredProduct();
    }
    public function allCategories()
    {
        return $this->buyerService->categories();
    }
    public function categoryBySlug($slug)
    {
        return $this->buyerService->categoryBySlug($slug);
    }
    public function getBanners()
    {
        return $this->buyerService->getBanners();
    }
    public function getHomeBanners()
    {
        return $this->buyerService->getHomeBanners();
    }
    public function getProducts()
    {
        return $this->buyerService->getProducts();
    }

    public function getProductDetail($slug)
    {
        return $this->buyerService->getProductDetail($slug);
    }
}
