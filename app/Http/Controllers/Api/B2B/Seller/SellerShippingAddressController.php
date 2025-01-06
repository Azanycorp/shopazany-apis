<?php

namespace App\Http\Controllers\Api\B2B\Seller;

use Illuminate\Http\Request;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\SellerShippingRequest;

class SellerShippingAddressController extends Controller
{
    public function __construct(
        private SellerService $service
    ) {}


    public function addShipping(SellerShippingRequest $request)
    {
        return $this->service->addShipping($request);
    }

    public function getAllShipping($user_id)
    {
        return $this->service->getAllShipping($user_id);
    }

    public function getShippingById($user_id, $shipping_id)
    {
        return $this->service->getShippingById($user_id, $shipping_id);
    }

    public function updateShipping(Request $request, $shipping_id)
    {
        return $this->service->updateShipping($request, $shipping_id);
    }

    public function deleteShipping($user_id, $shipping_id)
    {
        return $this->service->deleteShipping($user_id, $shipping_id);
    }

}
