<?php

namespace App\Trait;

use App\Enum\Coupon as EnumCoupon;
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


    /**
     * Normalize coupon input.
     * Convert invalid or placeholder values to null.
     *
     * @param mixed $coupon
     * @return string|null
     */
    protected function normalizeCoupon($coupon)
    {
        if (is_null($coupon) || trim(strtolower($coupon)) === 'null' || trim($coupon) === '') {
            return null;
        }
        return $coupon;
    }

    protected function handleReferrers(?string $referrerCode, $user)
    {
        if (!$referrerCode) {
            throw new \InvalidArgumentException('Referrer code is required');
        }

        $referrer = User::with(['wallet', 'referrer'])
            ->where('referrer_code', $referrerCode)
            ->first();

        if (!$referrer || !$referrer->is_affiliate_member) {
            throw new \Exception('You are not a valid referrer');
        }

        reward_user($referrer, 'create_account', 'completed', $user);
    }

    protected function validateCoupon($couponCode)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->where('status', EnumCoupon::ACTIVE)
            ->first();

        if (!$coupon) {
            throw new \Exception('Invalid coupon code or inactive');
        }

        if ($coupon->used) {
            throw new \Exception('Coupon has already been used');
        }

        if ($coupon->expire_at && $coupon->expire_at < now()) {
            throw new \Exception('Coupon has expired');
        }
    }

    protected function assignCoupon($couponCode, $user)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->where('status', EnumCoupon::ACTIVE)
            ->first();

        $coupon->update([
            'used' => 1,
            'used_by' => (object) [
                'user_id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
            ],
            'status' => EnumCoupon::INACTIVE
        ]);
    }
}




