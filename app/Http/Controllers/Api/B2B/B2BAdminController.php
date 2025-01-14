<?php

namespace App\Http\Controllers\Api\B2B;

use App\Enum\UserStatus;
use Illuminate\Http\Request;
use App\Services\B2B\AdminService;
use App\Http\Controllers\Controller;

class B2BAdminController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {}

    //RFQS
    public function allRfq()
    {
        return $this->adminService->getAllRfq();
    }
    public function rfqDetails($id)
    {
        return $this->adminService->getRfqDetails($id);
    }

    //Orders
    public function allOrders()
    {
        return $this->adminService->getAllOrders();
    }
    public function orderDetails($id)
    {
        return $this->adminService->getOrderDetails($id);
    }
    //profile
    public function adminProfile()
    {
        return $this->adminService->adminProfile();
    }
    public function updateAdminProfile(Request $request)
    {
        return $this->adminService->updateAdminProfile($request);
    }
    public function updateAdminPassword(Request $request)
    {
        return $this->adminService->updateAdminPassword($request);
    }
    public function enable2FA()
    {
        return $this->adminService->enableTwoFactor();
    }
}
