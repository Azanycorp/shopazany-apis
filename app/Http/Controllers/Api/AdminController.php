<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use App\Services\SuperAdminService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Requests\AdminUserRequest;
use Illuminate\Support\Facades\Request;
use App\Http\Requests\ShippingAgentRequest;
use App\Http\Requests\UpdateShippmentRequest;
use App\Http\Requests\VerificationCodeRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\OrderFinderRequest;
use App\Http\Requests\ChangeAdminPasswordRequest;
use App\Http\Requests\Admin\HubOrderFinderRequest;
use App\Http\Requests\Admin\CollationCentreRequest;

class AdminController extends Controller
{
    const MESSAGE = '403 Forbidden';

    public function __construct(
        private SuperAdminService $superAdminService
    ) {}


    public function deliveryOverview()
    {
        return $this->superAdminService->deliveryOverview();
    }

    // Collation Centers
    public function allCollationCentres()
    {
        return $this->superAdminService->allCollationCentres();
    }

    public function addCollationCentre(CollationCentreRequest $request)
    {
        return $this->superAdminService->addCollationCentre($request);
    }

    public function viewCollationCentre($id)
    {
        return $this->superAdminService->viewCollationCentre($id);
    }

    public function editCollationCentre(CollationCentreRequest $request, $id)
    {
        return $this->superAdminService->editCollationCentre($request, $id);
    }

    public function deleteCollationCentre($id)
    {
        return $this->superAdminService->deleteCollationCentre($id);
    }

    public function findCollationCentreOrder(OrderFinderRequest $request)
    {
        return $this->superAdminService->findCollationCentreOrder($request);
    }

    public function findPickupLocationOrder(HubOrderFinderRequest $request)
    {
        return $this->superAdminService->findPickupLocationOrder($request);
    }

    // Collation Centers Hubs
    public function allCollationCentreHubs()
    {
        return $this->superAdminService->allHubs();
    }

    public function addHub(HubRequest $request)
    {
        return $this->superAdminService->addHub($request);
    }

    public function viewHub($id)
    {
        return $this->superAdminService->viewHub($id);
    }

    public function editHub(HubRequest $request, $id)
    {
        return $this->superAdminService->editHub($request, $id);
    }

    public function deleteHub($id)
    {
        return $this->superAdminService->deleteHub($id);
    }

    // Admin Users
    public function adminUsers()
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->adminUsers();
    }

    public function addAdmin(AdminUserRequest $request)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->addAdmin($request);
    }

    public function viewAdminUser($id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->viewAdmin($id);
    }

    public function editAdminUser(Request $request, $id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->editAdmin($request, $id);
    }

    public function verifyPassword(Request $request)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->verifyPassword($request);
    }

    public function revokeAccess($id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->revokeAccess($id);
    }

    public function removeAdmin($id)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);

        return $this->superAdminService->removeAdmin($id);
    }

    // ShippingAgents section
    public function shippingAgents()
    {
        return $this->superAdminService->shippingAgents();
    }

    public function addShippingAgent(ShippingAgentRequest $request)
    {
        return $this->superAdminService->addShippingAgent($request);
    }

    public function viewShippingAgent($id)
    {
        return $this->superAdminService->viewShippingAgent($id);
    }

    public function editShippingAgent(ShippingAgentRequest $request, $id)
    {
        return $this->superAdminService->editShippingAgent($request, $id);
    }

    public function deleteShippingAgent($id)
    {
        return $this->superAdminService->deleteShippingAgent($id);
    }

    // profile
    public function adminProfile()
    {
        return $this->superAdminService->adminProfile();
    }

    public function updateAdminProfile(Request $request)
    {
        return $this->superAdminService->updateAdminProfile($request);
    }

    public function updateAdminPassword(ChangeAdminPasswordRequest $request)
    {
        return $this->superAdminService->updateAdminPassword($request);
    }

    public function enable2FA(Request $request)
    {
        return $this->superAdminService->enableTwoFactor($request);
    }

    public function sendCode()
    {
        return $this->superAdminService->sendCode();
    }

    public function verifyCode(VerificationCodeRequest $request)
    {
        return $this->superAdminService->verifyCode($request);
    }

    public function getNotifications()
    {
        return $this->superAdminService->getNotifications();
    }

    public function getNotification($id)
    {
        return $this->superAdminService->getNotification($id);
    }

    public function markRead($id)
    {
        return $this->superAdminService->markRead($id);
    }

    //Shippments
    public function allShippments()
    {
        return $this->superAdminService->allShippments();
    }

    public function findOrder(Request $request)
    {
        return $this->superAdminService->findOrder($request);
    }

    public function shippmentDetails($id)
    {
        return $this->superAdminService->shippmentDetails($id);
    }

    public function updateShippmentDetails(UpdateShippmentRequest $request, $id)
    {
        return $this->superAdminService->updateShippmentDetails($request, $id);
    }

    public function readyForDelivery(Request $request, $id)
    {
        return $this->superAdminService->readyForDelivery($request, $id);
    }

    public function readyToSender(Request $request, $id)
    {
        return $this->superAdminService->readyToSender($request, $id);
    }

    public function readyForPickup(Request $request, $id)
    {
        return $this->superAdminService->readyForPickup($request, $id);
    }

    public function readyForDispatched(Request $request, $id)
    {
        return $this->superAdminService->readyForDispatched($request, $id);
    }
}
