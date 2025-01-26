<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected \App\Services\SubscriptionService $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    public function getPlanByCountry($countryId)
    {
        return $this->service->getPlanByCountry($countryId);
    }

    public function subscriptionPayment(Request $request)
    {
        return $this->service->subscriptionPayment($request);
    }

    public function subscriptionHistory($userId)
    {
        return $this->service->subscriptionHistory($userId);
    }

}
