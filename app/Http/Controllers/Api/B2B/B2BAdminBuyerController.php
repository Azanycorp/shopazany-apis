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

    public function allBuyers()
    {

        return $this->buyerService->allBuyers();
    }

    public function viewBuyer($id)
    {
        return $this->buyerService->viewBuyer($id);
    }

    public function editBuyer(EditBuyerRequest $request, $id)
    {
        return $this->buyerService->editBuyer($request, $id);
    }

    public function editBuyerCompany(Request $request, $id)
    {
        return $this->buyerService->editBuyerCompany($request, $id);
    }

    public function banBuyer($id)
    {
        return $this->buyerService->banBuyer($id);
    }

    public function removeBuyer($id)
    {
        return $this->buyerService->removeBuyer($id);
    }

    public function approveBuyer($id)
    {
        return $this->buyerService->approveBuyer($id);
    }

    public function bulkRemoveBuyer(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        return $this->buyerService->bulkRemove($request);
    }
}
