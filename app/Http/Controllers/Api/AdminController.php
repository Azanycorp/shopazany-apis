<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Requests\BatchRequest;
use App\Services\SuperAdminService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Requests\AdminUserRequest;
use App\Http\Requests\ProcessBatchRequest;
use App\Http\Requests\ShippingAgentRequest;
use App\Http\Requests\UpdateShippmentRequest;
use App\Http\Requests\VerificationCodeRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\Admin\OrderFinderRequest;
use App\Http\Requests\TransferShippmentRequest;
use App\Http\Requests\ChangeAdminPasswordRequest;
use App\Http\Requests\Admin\HubOrderFinderRequest;
use App\Http\Requests\Admin\CollationCentreRequest;
use App\Http\Requests\DispatchBatchRequest;

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
    public function allShipments()
    {
        return $this->superAdminService->allShipments();
    }

    public function findOrder(Request $request)
    {
        return $this->superAdminService->findOrder($request);
    }

    public function shipmentDetails($id)
    {
        return $this->superAdminService->shipmentDetails($id);
    }

    public function updateShipmentDetails(UpdateShippmentRequest $request, $id)
    {
        return $this->superAdminService->updateShipmentDetails($request, $id);
    }

    public function readyForDelivery(Request $request, $id)
    {
        return $this->superAdminService->readyForDelivery($request, $id);
    }

    public function readyForPickup(Request $request, $id)
    {
        return $this->superAdminService->readyForPickup($request, $id);
    }

    public function returnToSender(Request $request, $id)
    {
        return $this->superAdminService->returnToSender($request, $id);
    }

    public function readyForDispatched(Request $request, $id)
    {
        return $this->superAdminService->readyForDispatched($request, $id);
    }

    public function transferShipment(TransferShippmentRequest $request, $id)
    {
        return $this->superAdminService->transferShipment($request, $id);
    }

    public function batchDetails($id)
    {
        return $this->superAdminService->batchDetails($id);
    }

    public function createBatch(BatchRequest $request)
    {
        return $this->superAdminService->createBatch($request);
    }

    public function dispatchBatch(DispatchBatchRequest $request, $id)
    {
        return $this->superAdminService->dispatchBatch($request, $id);
    }

    public function processBatch(ProcessBatchRequest $request, $id)
    {
        return $this->superAdminService->processBatch($request, $id);
    }
}
