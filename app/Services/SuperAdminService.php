<?php

namespace App\Services;

use App\Models\Admin;
use App\Trait\SignUp;
use App\Enum\UserType;
use App\Enum\AdminType;
use App\Enum\PlanStatus;
use App\Enum\UserStatus;
use App\Models\B2bOrder;
use App\Enum\AdminStatus;
use App\Enum\OrderStatus;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Models\PickupStation;
use App\Mail\B2BNewAdminEmail;
use App\Models\CollationCenter;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\HubResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Pipeline\Hub;
use App\Http\Resources\CollationCentreResource;

class SuperAdminService
{
    use HttpResponse, SignUp;

    public function getDashboardDetails()
    {
        $centers = CollationCenter::with(['country', 'hubs.country'])->latest('id')->get();

        $collation_counts = CollationCenter::selectRaw('
        COUNT(*) as total_centers,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_centers,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as inactive_centers
    ', [PlanStatus::ACTIVE, PlanStatus::INACTIVE])
            ->first();

        $collation_details = [
            'total_centers' => $collation_counts->total_centers ?? 0,
            'active_centers' => $collation_counts->active_centers ?? 0,
            'inactive_centers' => $collation_counts->inactive_centers ?? 0,
            'centers' => CollationCentreResource::collection($centers),
        ];
        return $this->success($collation_details, 'All available collation centres');
    }

     //Collation centers
    public function deliveryOverview()
    {
        $order_counts = B2bOrder::selectRaw('
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as out_for_delivery,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as delivered
    ', [OrderStatus::SHIPPED, OrderStatus::DELIVERED])
            ->first();
        $collation_centers = CollationCenter::where('status', PlanStatus::ACTIVE)->count();
        $hubs = PickupStation::where('status', PlanStatus::ACTIVE)->count();
        $details = [
            'total_shippments' => $order_counts->total_orders ?? 0,
            'out_for_delivery' => $order_counts->out_for_delivery ?? 0,
            'delivered' => $order_counts->delivered ?? 0,
            'hubs' => $hubs,
            'collation_centers' => $collation_centers,
        ];
        return $this->success($details, 'delivery overview');
    }
    public function allCollationCentres()
    {
        $total_centers = CollationCenter::count();
        $active_centers = CollationCenter::where('status', PlanStatus::ACTIVE)->count();
        $inactive_centers = CollationCenter::where('status', PlanStatus::INACTIVE)->count();
        $centers = CollationCenter::with(['country', 'hubs.country'])->latest('id')->get();
        $data = CollationCentreResource::collection($centers);
        $collation_details = [
            'total_centers' => $total_centers,
            'active_centers' => $active_centers,
            'inactive_centers' => $inactive_centers,
            'centers' => $data,
        ];
        return $this->success($collation_details, 'All available collation centres');
    }

    public function addCollationCentre($data)
    {
        $centre = CollationCenter::create([
            'name' => $data->name,
            'location' => $data->location,
            'status' => $data->status,
            'note' => $data->note,
            'city' => $data->city,
            'country_id' => $data->country_id,
            'status' => PlanStatus::ACTIVE
        ]);
        return $this->success($centre, 'Centre added successfully', 201);
    }

    public function viewCollationCentre($id)
    {
        $centre = CollationCenter::with(['country', 'hubs.country'])->find($id);
        if (!$centre) {
            return $this->error(null, 'Centre not found', 404);
        }

        $data = new CollationCentreResource($centre);
        return $this->success($data, 'Centre details');
    }

    public function editCollationCentre($id, $data)
    {
        $centre = CollationCenter::find($id);
        if (!$centre) {
            return $this->error(null, 'Centre not found', 404);
        }

        $centre->update([
            'name' => $data->name ?? $centre->name,
            'location' => $data->location ?? $centre->location,
            'note' => $data->note ?? $centre->note,
            'city' => $data->city ?? $centre->city,
            'country_id' => $data->country_id ?? $centre->country_id,
            'status' => $data->status ?? PlanStatus::ACTIVE
        ]);
        return $this->success(null, 'Details updated successfully');
    }

    public function deleteCollationCentre($id)
    {
        $centre = CollationCenter::find($id);

        if (!$centre) {
            return $this->error(null, 'Centre not found', 404);
        }
        if ($centre->hubs) {
            return $this->error(null, "Category can not be deleted because it has content", 422);
        }
        $centre->delete();
        return $this->success(null, 'Centre deleted successfully.');
    }

    // Hubs under Collation centers
    public function allCollationCentreHUbs()
    {
        $centers = PickupStation::with(['country', 'collationCenter'])->latest('id')->get();
        $data = HubResource::collection($centers);
        return $this->success($data, 'All available collation centres hubs');
    }

    public function addHub($data)
    {
        $hub = PickupStation::create([
            'collation_center_id' => $data->collation_center_id,
            'name' => $data->name,
            'location' => $data->location,
            'note' => $data->note,
            'city' => $data->city,
            'country_id' => $data->country_id,
            'status' => PlanStatus::ACTIVE
        ]);
        return $this->success($hub, 'Hub added successfully', 201);
    }

    public function viewHub($id)
    {
        $centre = PickupStation::with(['country', 'collationCenter'])->find($id);
        if (!$centre) {
            return $this->error(null, 'Hub not found', 404);
        }

        $data = new HubResource($centre);
        return $this->success($data, 'Hub details');
    }

    public function editHub($id, $data)
    {
        $hub = PickupStation::find($id);
        if (!$hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        $hub->update([
            'collation_center_id' => $data->collation_center_id ?? $hub->collation_center_id,
            'name' => $data->name ?? $hub->name,
            'location' => $data->location ?? $hub->location,
            'note' => $data->note ?? $hub->note,
            'city' => $data->city ?? $hub->city,
            'country_id' => $data->country_id ?? $hub->country_id,
            'status' => $data->status ?? PlanStatus::ACTIVE
        ]);
        return $this->success(null, 'Details updated successfully');
    }

    public function deleteHub($id)
    {
        $hub = PickupStation::find($id);

        if (!$hub) {
            return $this->error(null, 'Hub not found', 404);
        }
        $hub->delete();
        return $this->success(null, 'Hub deleted successfully.');
    }

    //Admins

    //Admin User Management
    public function adminUsers()
    {
        $searchQuery = request()->input('search');
        $admins = Admin::with('permissions:id,name')
            ->select('id', 'first_name', 'last_name', 'email', 'created_at')
            ->orderByDESC('created_at')
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                $queryBuilder->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('first_name', 'LIKE', '%' . $searchQuery . '%')
                        ->orWhere('email', 'LIKE', '%' . $searchQuery . '%');
                });
            })
            ->get();

        return $this->success($admins, 'All Admin Users');
    }

    public function addAdmin($data)
    {
        DB::beginTransaction();
        try {
            $password = Str::random(5);
            $admin = Admin::create([
                'first_name' => $data->first_name,
                'last_name' => $data->last_name,
                'email' => $data->email,
                'type' => $data->type,
                'status' => AdminStatus::ACTIVE,
                'phone_number' => $data->phone_number,
                'password' => bcrypt($password),
            ]);
            $admin->permissions()->sync($data->permissions);
            $loginDetails = [
                'name' => $data->first_name,
                'email' => $data->email,
                'password' => $password,
            ];
            DB::commit();

            defer(fn() => send_email($data->email, new B2BNewAdminEmail($loginDetails)));

            return $this->success($admin, 'Admin user added successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function viewAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        return $this->success($admin, 'Admin details');
    }

    public function editAdmin($id, $data)
    {
        $admin = Admin::findOrFail($id);
        $admin->update([
            'first_name' => $data->first_name,
            'last_name' => $data->last_name,
            'email' => $data->email,
            'type' => $data->type,
            'phone_number' => $data->phone_number,
        ]);
        $admin->roles()->sync($data->role_id);
        if ($data->permissions) {
            $admin->permissions()->sync($data->permissions);
        }
        return $this->success($admin, 'Details updated successfully');
    }

    public function verifyPassword($data)
    {
        $currentUserId = userAuthId();
        $admin = Admin::findOrFail($currentUserId);
        if (Hash::check($data->password, $admin->password)) {
            return $this->success(null, 'Password matched');
        }
        return $this->error(null, 'Password do not match');
    }
    public function revokeAccess($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->permissions()->detach();
        return $this->success(null, 'Access Revoked');
    }

    public function removeAdmin($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->permissions()->detach();
        $admin->delete();
        return $this->success(null, 'Deleted successfully');
    }
}
