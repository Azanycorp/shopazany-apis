<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\B2B\BuyerService;

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
    //Dasbaord
    public function dashboard()
    {
        return $this->buyerService->getDashboardDetails();
    }


}
