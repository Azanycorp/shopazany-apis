<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function dashboardAnalytics()
    {
        return $this->service->dashboardAnalytics();
    }

    public function bestSellers()
    {
        return $this->service->bestSellers();
    }
    
    public function bestSellingCat()
    {
        return $this->service->bestSellingCat();
    }













}
