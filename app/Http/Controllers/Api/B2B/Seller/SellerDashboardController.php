<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use App\Trait\HttpResponse;
use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;

class SellerDashboardController extends Controller
{
    use HttpResponse;
    public function __construct(
        private SellerService $service
    ) {}

    public function index()
    {
        return $this->service->getDashboardDetails();
    }

    public function getEarningReport()
    {
        return $this->service->getEarningReport();
    }
    
    public function withdrawalHistory()
    {
        return $this->service->getWithdrawalHistory();
    }
}
