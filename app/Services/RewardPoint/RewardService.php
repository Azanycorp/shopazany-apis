<?php

namespace App\Services\RewardPoint;

use App\Models\Action;
use App\Models\UserAction;

class RewardService
{
    public function rewardUser($user, $actionName, $status)
    {
        $action = Action::where('slug', $actionName)->firstOrFail();

        $userAction = UserAction::firstOrNew([
            'user_id' => $user->id,
            'action_id' => $action->id,
        ]);

        if (!$userAction->is_rewarded) {
            $userAction->is_rewarded = true;
            $userAction->points = $action->points;
            $userAction->status = $status;

            log_user_activity($user, $action, $status);

            $userAction->save();

            return $action->points;
        }

        return 0;
    }
}




