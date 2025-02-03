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

    public function approveSeller(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->service->approveSeller($request);
    }

    public function viewSeller($id)
    {
        return $this->service->viewSeller($id);
    }

    public function editSeller(Request $request, $id)
    {
        $request->validate([
            'first_name' => ['string', 'max:255'],
            'last_name' => ['string', 'max:255'],
            'email_address' => ['email', 'email:rfc,dns'],
            'phone_number' => ['string'],
            'password' => ['string', 'confirmed', Password::defaults()],
        ]);

        return $this->service->editSeller($request, $id);
    }

    public function banSeller(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->service->banSeller($request);
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

    public function viewSellerProduct($product_id, $user_id)
    {
        return $this->service->viewSellerProduct($product_id,$user_id);
    }

    public function editSellerProduct($id,Request $request)
    {
        return $this->service->editSellerProduct($id,$request);
    }

    public function removeSellerProduct($user_id, $product_id)
    {
        return $this->service->removeSellerProduct($user_id, $product_id);
    }
}
