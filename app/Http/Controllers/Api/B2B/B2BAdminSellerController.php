<?php

namespace App\Http\Controllers\Api\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\B2B\AddProductRequest;
use App\Services\B2B\AdminService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class B2BAdminSellerController extends Controller
{
    private const MESSAGE = '403 Forbidden';

    public function __construct(
        private readonly AdminService $service,
        private readonly Gate $gate,
    ) {}

    public function allSellers(Request $request)
    {
        // abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->service->allSellers($request);
    }

    public function approveSeller(int $id)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->approveSeller($id);
    }

    public function viewSeller(Request $request, int $id)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->viewSeller($request, $id);
    }

    public function banSeller(int $id)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->banSeller($id);
    }

    public function removeSeller(int $id)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->removeSeller($id);
    }

    public function bulkRemove(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        return $this->service->bulkRemove($request);
    }

    // Seller product section
    public function addSellerProduct(AddProductRequest $request)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->addSellerProduct($request);
    }

    public function viewSellerProduct(int $userId, int $productId)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->viewSellerProduct($userId, $productId);
    }

    public function editSellerProduct(Request $request, int $userId, int $productId)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->editSellerProduct($request, $userId, $productId);
    }

    public function removeSellerProduct(int $userId, int $productId)
    {
        abort_if($this->gate->denies('seller_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->removeSellerProduct($userId, $productId);
    }
}
