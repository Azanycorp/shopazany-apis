<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\BusinessInformationRequest;
use App\Http\Requests\B2B\BuyerOnboardingRequest;
use App\Http\Requests\B2B\CodeRequest;
use App\Http\Requests\B2B\SignupRequest;
use App\Http\Requests\B2B\VerifyCodeRequest;
use App\Http\Requests\LoginRequest;
use App\Services\B2B\Auth\AuthService;
use App\Services\B2B\BuyerService;
use App\Services\B2B\SellerService;
use Illuminate\Http\Request;

class B2BController extends Controller
{
    public function __construct(
        protected AuthService $service,
        protected SellerService $sellerService,
        protected BuyerService $buyerService,
    ) {}

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

    public function searchProduct(Request $request)
    {
        return $this->buyerService->searchProduct($request);
    }

    public function getSocialLinks(Request $request)
    {
        return $this->buyerService->getSocialLinks($request);
    }

    public function getCategoryProducts(Request $request)
    {
        return $this->buyerService->getCategoryProducts($request);
    }

    public function bestSellingProduct(Request $request)
    {
        return $this->buyerService->bestSelling($request);
    }

    public function featuredProduct(Request $request)
    {
        return $this->buyerService->featuredProduct($request);
    }

    public function allCategories(Request $request)
    {
        return $this->buyerService->categories($request);
    }

    public function getBlogs(Request $request)
    {
        return $this->buyerService->allBlogs($request);
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

    public function getClientLogos(Request $request)
    {
        return $this->buyerService->getClientLogos($request);
    }

    public function promoBanners()
    {
        return $this->buyerService->promoBanners();
    }

    public function getPageBanners($page)
    {
        return $this->buyerService->getPageBanners($page);
    }

    public function getProducts(Request $request)
    {
        return $this->buyerService->getProducts($request);
    }

    public function getProductDetail($slug)
    {
        return $this->buyerService->getProductDetail($slug);
    }
}
