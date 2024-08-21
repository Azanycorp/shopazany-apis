<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\User\CustomerService;
use Illuminate\Http\Request;

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
}
