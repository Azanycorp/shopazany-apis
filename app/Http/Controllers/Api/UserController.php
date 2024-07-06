<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
}