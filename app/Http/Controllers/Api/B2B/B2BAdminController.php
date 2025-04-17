<?php

namespace App\Http\Controllers\Api\B2B;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\B2B\AdminService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\Admin\BlogRequest;
use App\Http\Requests\SocialLinkRequest;
use App\Http\Requests\ShippingAgentRequest;
use App\Http\Requests\Admin\NewBannerRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
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
    public function getAllBanners()
    {
        return $this->adminService->getPageBanners();
    }

    public function addNewBanner(NewBannerRequest $request)
    {
        return $this->adminService->addPageBanner($request);
    }
    public function updatePageBanner(Request $request, $id)
    {
        return $this->adminService->updatePageBanner($request, $id);
    }
    public function deletePageBanner($id)
    {
        return $this->adminService->deletePageBanner($id);
    }
    public function editPageBanner($id)
    {
        return $this->adminService->getPageBanner($id);
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

    public function rejectProduct(Request $request, $id)
    {
        return $this->adminService->rejectProduct($request, $id);
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

    public function editSubscriptionPlan(SubscriptionPlanRequest $request, $id)
    {
        return $this->adminService->editSubscriptionPlan($request, $id);
    }

    public function deleteSubscriptionPlan($id)
    {
        return $this->adminService->deleteSubscriptionPlan($id);
    }

    //Blog Section
    public function getBlogs()
    {
        return $this->adminService->allBlogs();
    }

    public function addBlog(BlogRequest $request)
    {
        return $this->adminService->addBlog($request);
    }

    public function getBlog($id)
    {
        return $this->adminService->getBlog($id);
    }

    public function updateBlog(UpdateBlogRequest $request, $id)
    {
        return $this->adminService->updateBlog($request, $id);
    }

    public function deleteBlog($id)
    {
        return $this->adminService->deleteBlog($id);
    }


    //Social links section
    public function socialLinks()
    {
        return $this->adminService->getSocialLinks();
    }

    public function addLink(SocialLinkRequest $request)
    {
        return $this->adminService->addSocialLink($request);
    }

    public function viewLink($id)
    {
        return $this->adminService->viewLink($id);
    }

    public function editLink(SocialLinkRequest $request, $id)
    {
        return $this->adminService->editLink($request, $id);
    }

    public function deleteLink($id)
    {
        return $this->adminService->deleteLink($id);
    }
}
