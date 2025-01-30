<?php

namespace App\Services\RewardPoint;

use App\Models\Action;
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
        Action::create([
            'name' => $request->name,
            'slug' => $name,
            'points' => $request->points
        ]);
        return $this->success(null, "Added successfully");
    }

    public function getPoints()
    {
        $actions = Action::select('id', 'name', 'slug', 'points')->get();

        $data = $actions->map(function($action): array {
            return [
                'id' => $action->id,
                'name' => $action->name,
                'slug' => $action->slug,
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

        $name = strtolower(str_replace(' ', '_', $request->name));

        $action->update([
            'name' => $request->name,
            'slug' => $name,
            'points' => $request->points
        ]);

        return $this->success(null, "Updated successfully");
    }

    public function deletePoints($id)
    {
        Action::findOrFail($id)->delete();

        return $this->success(null, "Deleted successfully");
    }
}

