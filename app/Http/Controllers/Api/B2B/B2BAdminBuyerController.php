<?php

namespace App\Http\Controllers\Api\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditBuyerRequest;
use App\Services\B2B\AdminService;
use Illuminate\Http\Request;

class B2BAdminBuyerController extends Controller
{
    public function __construct(
        private AdminService $buyerService
    ) {}

    public function allBuyers(Request $request)
    {
        return $this->buyerService->allBuyers($request);
    }

    public function viewBuyer(int $id)
    {
        return $this->buyerService->viewBuyer($id);
    }

    public function editBuyer(EditBuyerRequest $request, int $id)
    {
        return $this->buyerService->editBuyer($request, $id);
    }

    public function editBuyerCompany(Request $request, int $id)
    {
        return $this->buyerService->editBuyerCompany($request, $id);
    }

    public function banBuyer(int $id)
    {
        return $this->buyerService->banBuyer($id);
    }

    public function removeBuyer(int $id)
    {
        return $this->buyerService->removeBuyer($id);
    }

    public function approveBuyer(int $id)
    {
        return $this->buyerService->approveBuyer($id);
    }

    public function bulkRemoveBuyer(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        return $this->buyerService->bulkRemove($request);
    }
}
