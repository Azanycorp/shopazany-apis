<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\User\CustomerService;
use App\Http\Requests\OrderRateRequest;
use App\Http\Requests\CustomerSupportRequest;
use App\Http\Controllers\Api\MailingListController;

class CustomerController extends Controller
{
    protected $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }

    public function dashboardAnalytics($userId)
    {
        return $this->service->dashboardAnalytics($userId);
    }

    public function acountOverview($userId)
    {
        return $this->service->acountOverview($userId);
    }

    public function recentOrders($userId)
    {
        return $this->service->recentOrders($userId);
    }

    public function getOrders($userId)
    {
        return $this->service->getOrders($userId);
    }

    public function getOrderDetail($orderNo)
    {
        return $this->service->getOrderDetail($orderNo);
    }

    public function rateOrder(OrderRateRequest $request)
    {
        return $this->service->rateOrder($request);
    }

    public function support(CustomerSupportRequest $request)
    {
        return $this->service->support($request);
    }

    public function wishlist(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'product_id' => ['required', 'integer']
        ]);

        return $this->service->wishlist($request);
    }

    public function getWishlist($userId)
    {
        return $this->service->getWishlist($userId);
    }

    public function getSingleWishlist($userId, $id)
    {
        return $this->service->getSingleWishlist($userId, $id);
    }

    public function removeWishlist($userId, $id)
    {
        return $this->service->removeWishlist($userId, $id);
    }

    public function rewardDashboard($userId)
    {
        return $this->service->rewardDashboard($userId);
    }

    public function activity($userId)
    {
        return $this->service->activity($userId);
    }
    
}
