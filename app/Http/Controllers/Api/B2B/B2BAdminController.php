<?php

namespace App\Http\Controllers\Api\B2B;

use App\Enum\UserStatus;
use Illuminate\Http\Request;
use App\Services\B2B\AdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\ChangeAdminPasswordRequest;

class B2BAdminController extends Controller
{
    public function __construct(
        private AdminService $adminService
    ) {}

    //dashboard
    public function dashboard()
    {
        return $this->adminService->dashboard();
    }
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
    public function allOrders(Request $request)
    {
        return $this->adminService->getAllOrders($request);
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

    public function updateAdminPassword(ChangeAdminPasswordRequest $request)
    {
        return $this->adminService->updateAdminPassword($request);
    }

    public function enable2FA(Request $request)
    {
        return $this->adminService->enableTwoFactor($request);
    }

    public function getConfigDetails()
    {
        return $this->adminService->getConfigDetails();
    }

    public function UpdateConfigDetails(Request $request)
    {
        return $this->adminService->UpdateConfigDetails($request);
    }

    //Withdrawal Requests
    public function widthrawalRequests()
    {
        return $this->adminService->widthrawalRequests();
    }

    public function viewWidthrawalRequest($id)
    {
        return $this->adminService->viewWidthrawalRequest($id);
    }

    public function approveWidthrawalRequest($id)
    {
        return $this->adminService->approveWidthrawalRequest($id);
    }

    public function cancelWidthrawalRequest($id)
    {
        return $this->adminService->cancelWidthrawalRequest($id);
    }

    //Withdrawal Method Requests
    public function widthrawalMethods()
    {
        return $this->adminService->widthrawalMethods();
    }

    public function viewWidthrawalMethod($id)
    {
        return $this->adminService->viewWidthrawalMethod($id);
    }

    public function approveWidthrawalMethod($id)
    {
        return $this->adminService->approveWidthrawalMethod($id);
    }

    public function rejectWidthrawalMethod(Request $request)
    {
        return $this->adminService->rejectWidthrawalMethod($request);
    }

    //Seller Products Approval Request
    public function allProducts()
    {
        return $this->adminService->allProducts();
    }

    public function viewProduct($id)
    {
        return $this->adminService->viewProduct($id);
    }

    public function approveProduct($id)
    {
        return $this->adminService->approveProduct($id);
    }

    public function rejectProduct(Request $request)
    {
        return $this->adminService->rejectProduct($request);
    }

    //Admin Users
    public function adminUsers()
    {
        return $this->adminService->adminUsers();
    }

    public function addAdmin(AdminUserRequest $request)
    {
        return $this->adminService->addAdmin($request);
    }

    public function viewAdminUser($id)
    {
        return $this->adminService->viewAdmin($id);
    }

    public function editAdminUser($id, Request $request)
    {
        return $this->adminService->editAdmin($id, $request);
    }

    public function removeAdmin($id)
    {
        return $this->adminService->removeAdmin($id);
    }
}
