<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\B2B\SuperAdminService;
use App\Http\Requests\Admin\CollationCentreRequest;

class AdminController extends Controller
{
    public function __construct(
        private SuperAdminService $superAdminService
    ) {}

    //Subscription plans
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
}
