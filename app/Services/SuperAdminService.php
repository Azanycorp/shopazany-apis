<?php

namespace App\Services;

use App\Trait\SignUp;
use App\Enum\PlanStatus;
use App\Trait\HttpResponse;
use App\Models\PickupStation;
use App\Models\CollationCenter;
use App\Http\Resources\HubResource;
use Illuminate\Contracts\Pipeline\Hub;
use App\Http\Resources\CollationCentreResource;

class SuperAdminService
{
    use HttpResponse, SignUp;

    // //Collation centers
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
}
