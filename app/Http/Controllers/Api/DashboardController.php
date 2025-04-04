<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    const MESSAGE = '403 Forbidden';

    public function __construct(
        protected DashboardService $service
    )
    {}

    public function dashboardAnalytics()
    {
        abort_if(Gate::denies('overview'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->dashboardAnalytics();
    }

    public function bestSellers()
    {
        abort_if(Gate::denies('overview'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->bestSellers();
    }

    public function bestSellingCat()
    {
        abort_if(Gate::denies('overview'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->bestSellingCat();
    }


}
