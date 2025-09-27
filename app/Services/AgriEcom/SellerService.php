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
        $user = User::with([
                'wallet',
                'bankAccount',
                'userbusinessinfo',
                'userSubscriptions',
                'userShippingAddress',
                'products',
            ])
            ->withCount('referrals')
            ->findOrFail($userId)
            ->append(['is_subscribed', 'subscription_plan']);

        $data = new ProfileResource($user);

        return $this->success($data, 'Profile');
    }

    public function editProfile($request)
    {
        $user = User::find($request->user_id);

        if (!$user) {
            return $this->error(null, 'User not found', 404);
        }

        if ($request->hasFile('image')) {
            $image = uploadUserImage($request, 'image', $user);
        }

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'middlename' => $request->middlename ?? $user->middlename,
            'email' => $request->email ?? $user->email,
            'date_of_birth' => $request->date_of_birth ?? $user->date_of_birth,
            'bio' => $request->bio ?? $user->bio,
            'gender' => $request->gender ?? $user->gender,
            'image' => $image['url'] ?? $user->image,
            'public_id' => $image['public_id'] ?? $user->public_id,
        ]);

        return $this->success(null, 'Profile updated successfully');
    }
}
