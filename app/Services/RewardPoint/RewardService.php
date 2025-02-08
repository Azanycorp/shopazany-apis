<?php

namespace App\Services\RewardPoint;

use App\Models\Action;
use App\Models\UserAction;

class RewardService
{
    public function rewardUser($user, $actionName, $status)
    {
        $action = Action::where('slug', $actionName)->first();
        if (!$action) {
            return 0;
        }

        $userAction = UserAction::firstOrNew([
            'user_id' => $user->id,
            'action_id' => $action->id,
        ]);

        if ($userAction->is_rewarded) {
            return 0;
        }

        $userAction->fill([
            'is_rewarded' => true,
            'points' => $action->points,
            'status' => $status,
        ]);
        $userAction->save();

        $wallet = $user->wallet()->firstOrCreate([], [
            'balance' => 0.00,
            'reward_point' => 0,
        ]);

        $wallet->increment('reward_point', $action->points);

        // Log user activity
        log_user_activity($user, $action, $status);

        return $action->points;
    }
}




