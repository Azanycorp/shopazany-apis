<?php

namespace App\Services\AgriEcom;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use App\Trait\HttpResponse;

class SellerService
{
    use HttpResponse;

    public function profile($userId)
    {
        $user = User::with(['wallet', 'bankAccount', 'userbusinessinfo', 'userSubscriptions', 'userShippingAddress'])
            ->withCount('referrals')
            ->findOrFail($userId)
            ->append(['is_subscribed', 'subscription_plan']);

        $data = new ProfileResource($user);

        return $this->success($data, 'Profile');
    }
}
