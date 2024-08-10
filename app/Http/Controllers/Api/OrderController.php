<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function orderAnalytics()
    {
        return $this->service->orderAnalytics();
    }

    public function localOrder()
    {
        return $this->service->localOrder();
    }

    public function intOrder()
    {
        return $this->service->intOrder();
    }

    public function orderDetail($id)
    {
        return $this->service->orderDetail($id);
    }

    public function searchOrder(Request $request)
    {
        return $this->service->searchOrder($request);
    }
}
