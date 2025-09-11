<?php

namespace App\Http\Controllers\Api\AgriEcom;

use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\WishListRequest;
use App\Http\Requests\LikeProductRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\B2BBuyerShippingAddressRequest;

class B2BBuyerController extends Controller
{

    public function __construct(
        protected BuyerService $buyerService
    ) {}

    // Account section
    public function profile()
    {
        return $this->buyerService->profile();
    }

    public function editAccount(Request $request)
    {
        return $this->buyerService->editAccount($request);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        return $this->buyerService->changePassword($request);
    }

    public function change2Fa(Request $request)
    {
        return $this->buyerService->change2FA($request);
    }

    public function editCompany(Request $request)
    {
        return $this->buyerService->editCompany($request);
    }

    public function getProducts()
    {
        return $this->buyerService->getAgriEcomProducts();
    }

    public function searchProduct()
    {
        return $this->buyerService->searchAgriEcomProducts();
    }

    public function featuredProduct()
    {
        return $this->buyerService->featuredProduct();
    }

    public function getProductDetail($slug)
    {
        return $this->buyerService->getProductDetail($slug);
    }

    // Wish list
    public function addTowishList(WishListRequest $request)
    {
        return $this->buyerService->addToWishList($request);
    }

    public function wishList()
    {
        return $this->buyerService->myWishList();
    }

    public function removeItem($id)
    {
        return $this->buyerService->removeItem($id);
    }

    public function likeProduct(LikeProductRequest $request)
    {
        return $this->buyerService->likeProduct($request);
    }

    public function requestQuote(Request $request)
    {
        return $this->buyerService->sendQuote($request);
    }

    // Shipping Address
    public function addShippingAddress(B2BBuyerShippingAddressRequest $request)
    {
        return $this->buyerService->addShippingAddress($request);
    }

    public function allShippingAddress()
    {
        return $this->buyerService->getAllShippingAddress();
    }

    public function getShippingAddress($id)
    {
        return $this->buyerService->getShippingAddress($id);
    }

    public function updateShippingAddress(Request $request, $id)
    {
        return $this->buyerService->updateShippingAddress($request, $id);
    }

    public function deleteShippingAddress($id)
    {
        return $this->buyerService->deleteShippingAddress($id);
    }

    public function setDefaultAddress($id)
    {
        return $this->buyerService->setDefaultAddress($id);
    }

    // Dasbaord
    public function dashboard()
    {
        return $this->buyerService->getDashboardDetails();
    }

    // Orders
    public function allOrders()
    {
        return $this->buyerService->allOrders();
    }

    public function getOrderDetails($id)
    {
        return $this->buyerService->orderDetails($id);
    }
}
