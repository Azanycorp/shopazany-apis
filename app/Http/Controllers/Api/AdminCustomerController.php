<?php

namespace App\Http\Controllers\Api;

use App\Enum\UserStatus;
use App\Http\Controllers\Controller;
use App\Services\Admin\CustomerService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    private const MESSAGE = '403 Forbidden';

    public function __construct(
        private readonly CustomerService $service,
        private readonly Gate $gate
    ) {}

    public function allCustomers(): array
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->allCustomers();
    }

    public function viewCustomer($id)
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->viewCustomer($id);
    }

    public function banCustomer(Request $request)
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        return $this->service->banCustomer($request);
    }

    public function removeCustomer(Request $request)
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        return $this->service->removeCustomer($request);
    }

    public function filter(): array
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->filter();
    }

    public function addCustomer(Request $request)
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'status' => [Rule::in([
                UserStatus::ACTIVE,
                UserStatus::BLOCKED,
                UserStatus::DELETED,
                UserStatus::PENDING,
                UserStatus::SUSPENDED,
            ])],
        ]);

        return $this->service->addCustomer($request);
    }

    public function editCustomer(Request $request)
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        $request->validate([
            'status' => [Rule::in([
                UserStatus::ACTIVE,
                UserStatus::BLOCKED,
                UserStatus::DELETED,
                UserStatus::PENDING,
                UserStatus::SUSPENDED,
            ])],
        ]);

        return $this->service->editCustomer($request);
    }

    public function getPayment($id)
    {
        abort_if($this->gate->denies('customer_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->service->getPayment($id);
    }
}
