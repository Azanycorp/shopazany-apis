<?php

namespace App\Services;

use App\Trait\SignUp;
use App\Enum\PlanStatus;
use App\Trait\HttpResponse;
use App\Models\PickupStation;
use App\Models\CollationCenter;
use Illuminate\Contracts\Pipeline\Hub;
use App\Http\Resources\CollationCentreResource;

class SuperAdminService
{
    use HttpResponse, SignUp;

    // //Collation centers
    public function allCollationCentres()
    {
        $centers = CollationCenter::latest('id')->get();
        $data = CollationCentreResource::collection($centers);
        return $this->success($data, 'All available collation centres');
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
        $data = new CollationCentreResource($centre);
        return $this->success($data, 'Centre added successfully', 201);
    }

    public function viewCollationCentre($id)
    {
        $centre = CollationCenter::find($id);
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
        $centre->delete();
        return $this->success(null, 'Centre deleted successfully.');
    }

    // Hubs under Collation centers
    public function allCollationCentreHUbs($id)
    {
        $centers = PickupStation::latest('id')->get();
        $data = CollationCentreResource::collection($centers);
        return $this->success($data, 'All available collation centres');
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
        $data = new HubResource($hub);
        return $this->success($data, 'Centre added successfully', 201);
    }

    public function viewHub($id)
    {
        $centre = CollationCenter::find($id);
        if (!$centre) {
            return $this->error(null, 'Centre not found', 404);
        }

        $data = new CollationCentreResource($centre);
        return $this->success($data, 'Centre details');
    }

    public function editHub($id, $data)
    {
        $hub = PickupStation::find($id);
        if (!$hub) {
            return $this->error(null, 'Hub not found', 404);
        }

        $hub->update([
            'collation_center_id' => $data->collation_center_id,
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
