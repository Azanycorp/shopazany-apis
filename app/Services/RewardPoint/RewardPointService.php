<?php

namespace App\Services\RewardPoint;

use App\Models\Action;
use App\Models\RewardPointSetting;
use App\Trait\HttpResponse;

class RewardPointService
{
    use HttpResponse;

    public function addPoints($request)
    {
        $name = strtolower(str_replace(' ', '_', $request->name));
        $existingAction = Action::where('name', $name)->first();

        if ($existingAction) {
            return $this->error(null, "The action with this name already exists.", 409);
        }

        $folder = app()->environment('production') ? '/prod/rewardpoint' : '/stag/rewardpoint';
        $url = uploadImage($request, 'icon', $folder);

        $countryIds = $request->country_ids;
        if (is_string($countryIds)) {
            $countryIds = explode(',', $countryIds);
        }
        $countryIds = array_map(fn($id) => (int) trim($id, '"'), $countryIds);

        Action::create([
            'name' => $request->name,
            'slug' => $name,
            'description' => $request->description,
            'icon' => $url,
            'verification_type' => $request->verification_type,
            'country_ids' => $countryIds,
            'points' => $request->points
        ]);

        return $this->success(null, "Added successfully");
    }

    public function getPoints()
    {
        $actions = Action::select(
            'id',
            'name',
            'slug',
            'description',
            'icon',
            'verification_type',
            'country_ids',
            'points'
        )->get();

        $data = $actions->map(function($action): array {
            return [
                'id' => $action->id,
                'name' => $action->name,
                'slug' => $action->slug,
                'description' => $action->description,
                'icon' => $action->icon,
                'verification_type' => $action->verification_type,
                'country_ids' => $action->country_ids,
                'points' => $action->points,
            ];
        });

        return $this->success($data, "Actions");
    }

    public function getOnePoints($id)
    {
        $action = Action::find($id);

        if(!$action){
            return $this->error(null, "Not found", 404);
        }

        $data = [
            'id' => $action->id,
            'name' => $action->name,
            'slug' => $action->slug,
            'description' => $action->description,
            'icon' => $action->icon,
            'verification_type' => $action->verification_type,
            'country_ids' => $action->country_ids,
            'points' => $action->points,
        ];

        return $this->success($data, "Action detail");
    }

    public function editPoints($request, $id)
    {
        $action = Action::find($id);

        if(!$action){
            return $this->error(null, "Not found", 404);
        }

        $folder = app()->environment('production') ? '/prod/rewardpoint' : '/stag/rewardpoint';

        $name = strtolower(str_replace(' ', '_', $request->name));
        $url = uploadImage($request, 'icon', $folder);

        if ($request->country_ids) {
            $countryIds = $request->country_ids;
            if (is_string($countryIds)) {
                $countryIds = explode(',', $countryIds);
            }
            $countryIds = array_map(fn($id) => (int) trim($id, '"'), $countryIds);
        }

        $action->update([
            'name' => $request->name ?? $action->name,
            'slug' => $name ?? $action->slug,
            'description' => $request->description ?? $action->description,
            'icon' => $url ?? $action->icon,
            'verification_type' => $request->verification_type ?? $action->verification_type,
            'country_ids' => $countryIds ?? $action->country_ids,
            'points' => $request->points ?? $action->points,
        ]);

        return $this->success(null, "Updated successfully");
    }

    public function deletePoints($id)
    {
        Action::findOrFail($id)->delete();

        return $this->success(null, "Deleted successfully");
    }

    public function addPointSetting($request)
    {
        RewardPointSetting::updateOrCreate(
            [
                'point' => $request->point,
            ],
            [
                'point' => $request->point,
                'value' => $request->value,
            ]
        );

        return $this->success(null, "Added successfully");
    }

    public function getPointSetting()
    {
        $pointSetting = RewardPointSetting::select('id', 'point', 'value')->first();
        return $this->success($pointSetting, "Point setting");
    }
}

