<?php

namespace App\Http\Controllers\Api;

use App\Enum\UserStatus;
use App\Http\Controllers\Controller;
use App\Services\Admin\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    protected $service;

    public function __construct(CustomerService $service)
    {
        $this->service = $service;
    }

    public function allCustomers()
    {
        return $this->service->allCustomers();
    }

    public function viewCustomer($id)
    {
        return $this->service->viewCustomer($id);
    }

    public function banCustomer(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id']
        ]);

        return $this->service->banCustomer($request);
    }

    public function removeCustomer($id)
    {
        return $this->service->removeCustomer($id);
    }

    public function filter()
    {
        return $this->service->filter();
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

        return $this->service->addCustomer($request);
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

        return $this->service->editCustomer($request);
    }

    public function getPayment($id)
    {
        return $this->service->getPayment($id);
    }

}
