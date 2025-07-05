<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Services\SuperAdminService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Admin\HubRequest;
use App\Http\Requests\AdminUserRequest;
use App\Http\Resources\AdminUserResource;
use App\Http\Requests\ShippingAgentRequest;
use Symfony\Component\HttpFoundation\Response;
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

    // Collation Centers Hubs
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

    public function adminProfile()
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)->firstOrFail();

        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile details');
    }

    public function updateAdminProfile($request)
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)
            ->firstOrFail();

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'email' => $request->email ?? $user->email,
            'phone_number' => $request->phone_number ?? $user->phone_number,
        ]);

        $data = new AdminUserResource($user);

        return $this->success($data, 'Profile detail');
    }

    public function enableTwoFactor($request)
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)
            ->firstOrFail();

        $user->update([
            'two_factor_enabled' => $request->two_factor_enabled,
        ]);

        return $this->success('Settings updated');
    }

    public function updateAdminPassword($request)
    {
        $authUser = userAuth();
        $user = Admin::where('id', $authUser->id)
            ->firstOrFail();

        if (!Hash::check($request->old_password, $user->password)) {
            return $this->error('Old password is incorrect.', 400);
        }

        $user->update([
            'password' => bcrypt($request->password),
        ]);

        return $this->success(null, 'Password updated');
    }
}
