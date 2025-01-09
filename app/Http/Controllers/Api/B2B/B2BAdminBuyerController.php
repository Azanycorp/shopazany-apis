<?php

namespace App\Http\Controllers\Api\B2B;

use App\Enum\UserStatus;
use Illuminate\Http\Request;
use App\Services\B2B\BuyerService;
use App\Http\Controllers\Controller;

class B2BAdminBuyerController extends Controller
{

    public function __construct(
        private BuyerService $buyerService)
    {}
    public function allCustomers()
    {
        return $this->buyerService->allCustomers();
    }

    public function viewCustomer($id)
    {
        return $this->buyerService->viewCustomer($id);
    }

    public function banCustomer(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->buyerService->banCustomer($request);
    }

    public function removeCustomer(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        return $this->buyerService->removeCustomer($request);
    }

    public function filter()
    {
        return $this->buyerService->filter();
    }

    public function addCustomer(Request $request)
    {
        $request->validate([
            'status' => [Rule::in([
                UserStatus::ACTIVE,
                UserStatus::BLOCKED,
                UserStatus::DELETED,
                UserStatus::PENDING,
                UserStatus::SUSPENDED
            ])],
        ]);

        return $this->buyerService->addCustomer($request);
    }

    public function editCustomer(Request $request)
    {
        $request->validate([
            'status' => [Rule::in([
                UserStatus::ACTIVE,
                UserStatus::BLOCKED,
                UserStatus::DELETED,
                UserStatus::PENDING,
                UserStatus::SUSPENDED
            ])],
        ]);

        return $this->buyerService->editCustomer($request);
    }

    public function getPayment($id)
    {
        return $this->buyerService->getPayment($id);
    }

}
