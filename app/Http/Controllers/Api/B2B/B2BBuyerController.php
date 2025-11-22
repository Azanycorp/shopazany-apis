<?php

namespace App\Http\Controllers\Api\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2BBuyerShippingAddressRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LikeProductRequest;
use App\Http\Requests\QuoteRequest;
use App\Http\Requests\WishListRequest;
use App\Services\B2B\BuyerService;
use Illuminate\Http\Request;

class B2BBuyerController extends Controller
{
    public function __construct(
        protected BuyerService $buyerService
    ) {}

    public function requestRefund(Request $request)
    {
        return $this->buyerService->requestRefund($request);
    }

    public function requestQuote(LikeProductRequest $request)
    {
        return $this->buyerService->sendQuote($request);
    }

    // Quotes
    public function allQuotes()
    {
        return $this->buyerService->allQuotes();
    }

    public function sendAllQuotes(Request $request)
    {
        return $this->buyerService->sendMutipleQuotes($request);
    }

    public function sendSingleQuote(QuoteRequest $request)
    {
        return $this->buyerService->sendRfq($request);
    }

    public function removeQuote(int $id)
    {
        return $this->buyerService->removeQuote($id);
    }

    // Dasbaord
    public function dashboard()
    {
        return $this->buyerService->getDashboardDetails();
    }

    // Orders
    public function allOrders(Request $request)
    {
        return $this->buyerService->allOrders($request);
    }

    public function getOrderDetails($id)
    {
        return $this->buyerService->orderDetails($id);
    }

    // Rfq
    public function getAllRfqs()
    {
        return $this->buyerService->allRfqs();
    }

    public function getRfqDetails($id)
    {
        return $this->buyerService->rfqDetails($id);
    }

    public function reviewRequest(Request $request)
    {
        return $this->buyerService->sendReviewRequest($request);
    }

    public function acceptQuote(Request $request)
    {
        return $this->buyerService->acceptQuote($request);
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

    public function sendFromWishList(Request $request)
    {
        return $this->buyerService->sendFromWishList($request);
    }

    // Product Review
    public function addReview(Request $request)
    {
        return $this->buyerService->addReview($request);
    }

    // Product Review
    public function likeProduct(LikeProductRequest $request)
    {
        return $this->buyerService->likeProduct($request);
    }

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
}
