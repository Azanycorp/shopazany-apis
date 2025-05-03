<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Requests\LoginRequest;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\CodeRequest;
use App\Services\B2B\Auth\AuthService;
use App\Http\Requests\B2B\SignupRequest;
use App\Http\Requests\B2B\VerifyCodeRequest;
use App\Http\Requests\B2B\BuyerOnboardingRequest;
use App\Http\Requests\B2B\BusinessInformationRequest;

class B2BController extends Controller
{
    public function __construct(
        protected AuthService $service,
        protected SellerService $sellerService,
        protected BuyerService $buyerService,
    )
    {}

    public function login(LoginRequest $request)
    {
        return $this->service->login($request);
    }

    public function signup(SignupRequest $request)
    {
        return $this->service->signup($request);
    }

    public function verify(VerifyCodeRequest $request)
    {
        return $this->service->verify($request);
    }

    public function resendCode(CodeRequest $request)
    {
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
    public function getSocialLinks()
    {
        return $this->buyerService->getSocialLinks();
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

    public function getBlogs()
    {
        return $this->buyerService->allBlogs();
    }

    public function getBlogDetails($slug)
    {
        return $this->buyerService->singleBlog($slug);
    }

    public function categoryBySlug($slug)
    {
        return $this->buyerService->categoryBySlug($slug);
    }

    public function getSliders()
    {
        return $this->buyerService->getSliders();
    }

    public function getBanners()
    {
        return $this->buyerService->getBanners();
    }
    public function getClientLogos()
    {
        return $this->buyerService->getClientLogos();
    }

    public function promoBanners(SellerService $sellerService)
    {
        return $this->buyerService->promoBanners($sellerService);
    }

    public function getPageBanners($page)
    {
        return $this->buyerService->getPageBanners($page);
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
