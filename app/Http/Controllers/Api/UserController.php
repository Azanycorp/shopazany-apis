<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AffiliateSettingsRequest;
use App\Http\Requests\BankAccountRequest;
use App\Http\Requests\KycRequest;
use App\Http\Requests\WithdrawalRequest;
use App\Services\User\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $userService)
    {
        $this->service = $userService;
    }

    public function profile()
    {
        return $this->service->profile();
    }

    public function updateProfile(Request $request, $userId)
    {
        return $this->service->updateProfile($request, $userId);
    }

    public function bankAccount(BankAccountRequest $request)
    {
        return $this->service->bankAccount($request);
    }

    public function removeBankAccount(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->service->removeBankAccount($request);
    }

    public function withdraw(WithdrawalRequest $request)
    {
        return $this->service->withdraw($request);
    }

    public function userKyc(KycRequest $request)
    {
        return $this->service->userKyc($request);
    }

    public function earningOption(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required', 'in:payment,commision']
        ], [
            'type' => "Type should either be payment or commision"
        ]);

        return $this->service->earningOption($request);
    }

    public function dashboardAnalytic($id)
    {
        return $this->service->dashboardAnalytic($id);
    }

    public function transactionHistory($userId)
    {
        return $this->service->transactionHistory($userId);
    }

    public function addPaymentMethod(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'type' => ['required']
        ]);

        return $this->service->addPaymentMethod($request);
    }

    public function getPaymentMethod($userId)
    {
        return $this->service->getPaymentMethod($userId);
    }

    public function changeSettings(AffiliateSettingsRequest $request, $userId)
    {
        return $this->service->changeSettings($request, $userId);
    }
}
