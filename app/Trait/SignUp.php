<?php

namespace App\Trait;

use App\Enum\UserType;
use App\Models\Coupon;
use App\Models\User;

trait SignUp
{
    protected function createUser($request)
    {
        $code = generateVerificationCode();
        return User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => UserType::CUSTOMER,
            'email_verified_at' => null,
            'verification_code' => $code,
            'is_verified' => 0,
            'password' => bcrypt($request->password),
        ]);
    }

    protected function handleReferrer($referrerCode, $user)
    {
        $referrer = User::where('referrer_code', $referrerCode)->first();
        if ($referrer) {
            reward_user($referrer, 'referral', 'completed');
        }
    }

    protected function validateAndAssignCoupon($couponCode, $user)
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            throw new \Exception('Invalid coupon code');
        }

        if ($coupon->used) {
            throw new \Exception('Coupon has already been used');
        }
        
        if ($coupon->expires_at && $coupon->expires_at < now()) {
            throw new \Exception('Coupon has expired');
        }

        $coupon->update([
            'used' => 1,
            'used_by' => (object)[
                'user_id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
            ],
        ]);
    }
}




