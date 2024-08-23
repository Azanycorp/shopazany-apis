<?php

namespace App\Services\RewardPoint;

use App\Models\Action;
use App\Models\UserAction;

class RewardService
{
    public function rewardUser($user, $actionName)
    {
        $action = Action::where('name', $actionName)->firstOrFail();
        $userAction = UserAction::firstOrNew([
            'user_id' => $user->id,
            'action_id' => $action->id,
        ]);

        if (!$userAction->is_rewarded) {
            $userAction->is_rewarded = true;
            $userAction->save();

            $user->points += $action->points;
            $user->save();

            return $action->points;
        }

        return 0;
    }
}




