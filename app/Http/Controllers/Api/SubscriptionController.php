<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionPaymentRequest;
use App\Services\SubscriptionService;
use App\Trait\HttpResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    use HttpResponse;

    protected \App\Services\SubscriptionService $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    public function getPlanByCountry($countryId)
    {
        return $this->service->getPlanByCountry($countryId);
    }

    public function subscriptionPayment(SubscriptionPaymentRequest $request)
    {
        return $this->service->subscriptionPayment($request);
    }

    public function subscriptionHistory($userId)
    {
        return $this->service->subscriptionHistory($userId);
    }

}
