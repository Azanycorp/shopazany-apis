<?php

namespace App\Http\Controllers\Api\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;
use App\Services\B2B\AdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class B2BAdminSellerController extends Controller
{
    protected AdminService $service;

    const MESSAGE = '403 Forbidden';

    public function __construct(AdminService $service)
    {
        $this->service = $service;
    }

    public function allSellers()
    {
        // abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->allSellers();
    }

    public function approveSeller($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->approveSeller($id);
    }

    public function viewSeller($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->viewSeller($id);
    }

    public function banSeller($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->banSeller($id);
    }

    public function removeSeller($id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

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

    // Seller product section
    public function addSellerProduct(AddProductRequest $request)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addSellerProduct($request);
    }

    public function viewSellerProduct($user_id, $product_id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->viewSellerProduct($user_id, $product_id);
    }

    public function editSellerProduct(Request $request, $user_id, $product_id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->editSellerProduct($request, $user_id, $product_id);
    }

    public function removeSellerProduct($user_id, $product_id)
    {
        abort_if(Gate::denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->removeSellerProduct($user_id, $product_id);
    }
}
