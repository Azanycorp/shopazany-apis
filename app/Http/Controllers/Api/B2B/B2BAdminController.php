<?php

namespace App\Http\Controllers\Api\B2B;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Http\Requests\Admin\ClientLogoRequest;
use App\Http\Requests\Admin\NewBannerRequest;
use App\Http\Requests\Admin\SubscriptionPlanRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\ChangeAdminPasswordRequest;
use App\Http\Requests\ShippingAgentRequest;
use App\Http\Requests\SocialLinkRequest;
use App\Services\B2B\AdminService;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class B2BAdminController extends Controller
{
    private const MESSAGE = '403 Forbidden';

    public function __construct(
        private readonly AdminService $adminService,
        private readonly Gate $gate,
    ) {}

    // dashboard
    public function dashboard()
    {
        return $this->adminService->dashboard();
    }

    public function agriEComDashboard()
    {
        return $this->adminService->agriEComDashboard();
    }

    // RFQS
    public function allRfq()
    {
        return $this->adminService->getAllRfq();
    }

    public function getAgriecomRfq()
    {
        return $this->adminService->getAgriecomRfq();
    }

    public function rfqDetails($id)
    {
        return $this->adminService->getRfqDetails($id);
    }

    // Orders
    public function allOrders()
    {
        abort_if($this->gate->denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->getAllOrders();
    }

    public function markCompleted($id)
    {
        abort_if($this->gate->denies('mark_complete'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->markCompleted($id);
    }

    public function cancelOrder($id)
    {
        abort_if($this->gate->denies('cancel_order'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->cancelOrder($id);
    }

    public function orderDetails($id)
    {
        abort_if($this->gate->denies('order_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->getOrderDetails($id);
    }

    // profile
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

    // Seller Products Approval Request
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

    // Subscription plans
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

    // Blog Section
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

    // Client Logoff Section
    public function allClientLogos()
    {
        return $this->adminService->allClientLogos();
    }

    public function addClientLogo(ClientLogoRequest $request)
    {
        return $this->adminService->addClientLogo($request);
    }

    public function getClientLogo($id)
    {
        return $this->adminService->getClientLogo($id);
    }

    public function updateClientLogo(Request $request, $id)
    {
        return $this->adminService->updateClientLogo($request, $id);
    }

    public function deleteClientLogo($id)
    {
        return $this->adminService->deleteClientLogo($id);
    }

    // Social links section

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

    // Admin Users
    public function adminUsers()
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->adminUsers();
    }

    public function addAdmin(AdminUserRequest $request)
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->addAdmin($request);
    }

    public function viewAdminUser($id)
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->viewAdmin($id);
    }

    public function editAdminUser(Request $request, $id)
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->editAdmin($request, $id);
    }

    public function verifyPassword(Request $request)
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->verifyPassword($request);
    }

    public function revokeAccess($id)
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->revokeAccess($id);
    }

    public function removeAdmin($id)
    {
        abort_if($this->gate->denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->adminService->removeAdmin($id);
    }

    // ShippingAgents section
    public function shippingAgents()
    {
        return $this->adminService->shippingAgents();
    }

    public function addShippingAgent(ShippingAgentRequest $request)
    {
        return $this->adminService->addShippingAgent($request);
    }

    public function viewShippingAgent($id)
    {
        return $this->adminService->viewShippingAgent($id);
    }

    public function editShippingAgent(ShippingAgentRequest $request, $id)
    {
        return $this->adminService->editShippingAgent($request, $id);
    }

    public function deleteShippingAgent($id)
    {
        return $this->adminService->deleteShippingAgent($id);
    }
}
