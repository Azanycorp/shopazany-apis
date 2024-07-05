<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankAccountRequest;
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
}
