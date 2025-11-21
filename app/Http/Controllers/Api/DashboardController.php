<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    private const MESSAGE = '403 Forbidden';

    public function __construct(
        protected DashboardService $service,
        private readonly Gate $gate
    ) {}

    public function dashboardAnalytics(Request $request)
    {
        abort_if($this->gate->denies('overview'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->dashboardAnalytics($request);
    }

    public function bestSellers()
    {
        abort_if($this->gate->denies('overview'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->bestSellers();
    }

    public function bestSellingCat()
    {
        abort_if($this->gate->denies('overview'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->bestSellingCat();
    }
}
