<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Admin\CustomerService;
use Illuminate\Http\Request;

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

}
