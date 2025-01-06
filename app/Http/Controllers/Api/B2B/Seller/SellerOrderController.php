<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use App\Trait\HttpResponse;
use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SellerOrderController extends Controller
{
    use HttpResponse;
    public function __construct(
        private SellerService $service
    ) {}
    //Orders
    public function index()
    {
        return $this->service->getAllOrders();
    }

    public function details($id)
    {
        return $this->service->getOrderDetails($id);
    }

    //RFQS
    public function allRfq()
    {
        return $this->service->getAllRfq();
    }
    public function rfqDetails($id)
    {
        return $this->service->getRfqDetails($id);
    }
}
