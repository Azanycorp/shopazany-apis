<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;

class SellerComplaintsController extends Controller
{
    public function __construct(
        private SellerService $service
    ) {}

    public function getComplaints($user_id)
    {
        return $this->service->getComplaints($user_id);
    }
}
