<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\SuperAdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Requests\Admin\CollationCentreRequest;

class AdminController extends Controller
{
    public function __construct(
        private SuperAdminService $superAdminService
    ) {}

    public function dashboard()
    {
        return $this->superAdminService->getDashboardDetails();
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
}
