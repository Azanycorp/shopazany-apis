<?php

namespace App\Services\RewardPoint;

use App\Enum\UserType;
use App\Models\Action;
use App\Models\UserAction;

class RewardService
{
    public function rewardUser($user, $actionName, $status, $newUser = null)
    {
        $action = Action::where('slug', $actionName)->first();
        if (! $action) {
            return 0;
        }

        $userAction = UserAction::firstOrNew([
            'user_id' => $user->id,
            'action_id' => $action->id,
        ]);

        $userAction->fill([
            'is_rewarded' => true,
            'points' => $action->points,
            'status' => $status,
        ]);
        $userAction->save();

        if ($user->is_affiliate_member) {
            $wallet = $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 0.00,
                    'reward_point' => 0,
                ]
            );

            $points = $action->points ?? 0;
            $wallet->increment('reward_point', $points);
            $wallet->refresh();

            $user->referrals()->syncWithoutDetaching($newUser);
        }

        if ($user->type === UserType::CUSTOMER) {
            $wallet = $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 0.00,
                    'reward_point' => 0,
                ]
            );

            $points = $action->points ?? 0;
            $wallet->increment('reward_point', $points);
            $wallet->refresh();
        }

        log_user_activity($user, $action, $status);

        return $action->points;
    }
}
