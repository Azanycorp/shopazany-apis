<?php

namespace App\Services\B2B;

use App\Trait\SignUp;
use App\Enum\PlanStatus;
use App\Trait\HttpResponse;
use App\Models\CollationCenter;
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
            'name' => $data->name,
            'location' => $data->location,
            'note' => $data->note,
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
}
