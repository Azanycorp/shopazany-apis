<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\B2B\AdminService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\ShippingAgentRequest;
use App\Http\Requests\ChangeAdminPasswordRequest;
use App\Http\Requests\Admin\SubscriptionPlanRequest;

class B2BAdminController extends Controller
{
    const MESSAGE = '403 Forbidden';
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
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->getAllOrders($request);
    }
    public function markCompleted($id)
    {
        abort_if(Gate::denies('mark_complete'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->markCompleted($id);
    }
    public function cancelOrder($id)
    {
        abort_if(Gate::denies('cancel_order'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->cancelOrder($id);
    }

    public function orderDetails($id)
    {
        abort_if(Gate::denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
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

    public function rejectWidthrawalMethod($id, Request $request)
    {
        return $this->adminService->rejectWidthrawalMethod($id, $request);
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

    public function rejectProduct($id, Request $request)
    {
        return $this->adminService->rejectProduct($id, $request);
    }

    //Admin Users
    public function adminUsers()
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->adminUsers();
    }

    public function addAdmin(AdminUserRequest $request)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->addAdmin($request);
    }

    public function viewAdminUser($id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->viewAdmin($id);
    }

    public function editAdminUser($id, Request $request)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->editAdmin($id, $request);
    }

    public function verifyPassword(Request $request)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->verifyPassword($request);
    }

    public function revokeAccess($id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->revokeAccess($id);
    }

    public function removeAdmin($id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->adminService->removeAdmin($id);
    }

    //ShippingAgents section
    public function shippingAgents()
    {
        return $this->adminService->shippingAgents();
    }

    public function addShippingAgent(ShippingAgentRequest $request)
    {
        return $this->adminService->addShippingAgent($request);
    }
    public function getCountryList()
    {
        return $this->adminService->getCountryList();
    }

    public function viewShippingAgent($id)
    {
        return $this->adminService->viewShippingAgent($id);
    }

    public function editShippingAgent($id, ShippingAgentRequest $request)
    {
        return $this->adminService->editShippingAgent($id, $request);
    }

    public function deleteShippingAgent($id)
    {
        return $this->adminService->deleteShippingAgent($id);
    }

    //Subscription plans
    public function b2bSubscriptionPlans()
    {
        return $this->adminService->b2bSubscriptionPlans();
    }

    public function addSubscriptionPlan(SubscriptionPlanRequest $request)
    {
        return $this->adminService->addSubscriptionPlan($request);
    }


    public function viewSubscriptionPlan($id)
    {
        return $this->adminService->viewSubscriptionPlan($id);
    }

    public function editSubscriptionPlan($id, SubscriptionPlanRequest $request)
    {
        return $this->adminService->editSubscriptionPlan($id, $request);
    }

    public function deleteSubscriptionPlan($id)
    {
        return $this->adminService->deleteSubscriptionPlan($id);
    }
}
