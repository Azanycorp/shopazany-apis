<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;

class B2BBuyerController extends Controller
{
    protected $buyerService;

    public function __construct(BuyerService $buyerService)
    {
        $this->buyerService = $buyerService;
    }

    public function requestRefund(Request $request)
    {
        return $this->buyerService->requestRefund($request);
    }

    public function requestQuote(Request $request)
    {
        return $this->buyerService->sendQuote($request);
    }
    //Quotes
    public function allQuotes()
    {
        return $this->buyerService->allQuotes();
    }
    public function sendAllQuotes()
    {
        return $this->buyerService->sendMutipleQuotes();
    }
    public function sendSingleQuote($id)
    {
        return $this->buyerService->sendRfq($id);
    }
    public function removeQuote($id)
    {
        return $this->buyerService->removeQuote($id);
    }
    //Dasbaord
    public function dashboard()
    {
        return $this->buyerService->getDashboardDetails();
    }
    //RFQ
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
    //Wish list
    public function addTowishList(Request $request)
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
    //Account section
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

    public function editCompany(Request $request)
    {
        return $this->buyerService->editCompany($request);
    }
}
