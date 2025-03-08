<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\SuperAdminService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\ShippingAgentRequest;
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
    //Collation Centers
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

    public function editCollationCentre($id, CollationCentreRequest $request)
    {
        return $this->superAdminService->editCollationCentre($id, $request);
    }

    public function deleteCollationCentre($id)
    {
        return $this->superAdminService->deleteCollationCentre($id);
    }
    //Collation Centers Hubs
    public function allCollationCentreHubs()
    {
        return $this->superAdminService->allCollationCentreHubs();
    }

    public function addHub(HubRequest $request)
    {
        return $this->superAdminService->addHub($request);
    }

    public function viewHub($id)
    {
        return $this->superAdminService->viewHub($id);
    }

    public function editHub($id, HubRequest $request)
    {
        return $this->superAdminService->editHub($id, $request);
    }

    public function deleteHub($id)
    {
        return $this->superAdminService->deleteHub($id);
    }


    //Admin Users
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

    public function editAdminUser($id, Request $request)
    {
        abort_if(Gate::denies('user_management'), Response::HTTP_FORBIDDEN, self::MESSAGE);
        return $this->superAdminService->editAdmin($id, $request);
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


    //ShippingAgents section
    public function shippingAgents()
    {
        return $this->superAdminService->shippingAgents();
    }

    public function addShippingAgent(ShippingAgentRequest $request)
    {
        return $this->superAdminService->addShippingAgent($request);
    }
    public function getCountryList()
    {
        return $this->superAdminService->getCountryList();
    }

    public function viewShippingAgent($id)
    {
        return $this->superAdminService->viewShippingAgent($id);
    }

    public function editShippingAgent($id, ShippingAgentRequest $request)
    {
        return $this->superAdminService->editShippingAgent($id, $request);
    }

    public function deleteShippingAgent($id)
    {
        return $this->superAdminService->deleteShippingAgent($id);
    }

}
