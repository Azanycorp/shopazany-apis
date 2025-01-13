<?php

namespace App\Http\Controllers\Api\B2B;

use App\Enum\UserStatus;
use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Controllers\Controller;
use App\Services\B2B\AdminService;

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
