<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use App\Services\B2B\AdminService;
use App\Services\B2B\SellerService;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
use App\Http\Requests\B2B\AddProductRequest;

class B2BAdminSellerController extends Controller
{

    protected \App\Services\B2B\AdminService $service;

    public function __construct(AdminService $service)
    {
        $this->service = $service;
    }

    public function allSellers()
    {
        return $this->service->allSellers();
    }

    public function approveSeller($id)
    {
        return $this->service->approveSeller($id);
    }

    public function viewSeller($id)
    {
        return $this->service->viewSeller($id);
    }

   
    public function banSeller($id)
    {
        return $this->service->banSeller($id);
    }

    public function removeSeller($id)
    {
        return $this->service->removeSeller($id);
    }

    public function bulkRemove(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        return $this->service->bulkRemove($request);
    }

    //Seller product section
    public function addSellerProduct(AddProductRequest $request)
    {
        return $this->service->addSellerProduct($request);
    }

    public function viewSellerProduct($user_id,$product_id)
    {
        return $this->service->viewSellerProduct($user_id,$product_id,);
    }

    public function editSellerProduct($user_id,$product_id,Request $request)
    {
        return $this->service->editSellerProduct($user_id,$product_id,$request);
    }

    public function removeSellerProduct($user_id, $product_id)
    {
        return $this->service->removeSellerProduct($user_id, $product_id);
    }
}
