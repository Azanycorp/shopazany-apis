<?php

namespace App\Trait;

use App\Enum\Coupon as EnumCoupon;
use App\Enum\UserType;
use App\Models\Country;
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

        $referrer = User::with(['wallet', 'referrals'])
            ->where('referrer_code', $referrerCode)
            ->first();

        if (!$referrer || !$referrer->is_affiliate_member) {
            throw new \Exception('You are not a valid referrer');
        }

        reward_user($referrer, 'referral', 'completed', $user);
    }

    protected function validateCoupon($couponCode)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->whereStatus(EnumCoupon::ACTIVE)
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
            ->whereStatus(EnumCoupon::ACTIVE)
            ->lockForUpdate()
            ->first();

        if (!$coupon) {
            return $this->error("Invalid or expired coupon", 400);
        }

        $coupon->total_used = $coupon->total_used ?? 0;
        $usedBy = $coupon->used_by ?? [];

        $newUserEntry = [
            'user_id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
        ];

        if ($coupon->type === EnumCoupon::MULTI_USE) {
            $coupon->increment('total_used');
            $usedBy[] = $newUserEntry;
        } else {
            $usedBy = [$newUserEntry];
            $coupon->total_used = 1;
        }

        if ($coupon->type === EnumCoupon::ONE_TIME || $coupon->total_used >= $coupon->max_use) {
            $coupon->status = EnumCoupon::INACTIVE;
        }

        $coupon->update([
            'used' => ($coupon->status === EnumCoupon::INACTIVE) ? 1 : 0,
            'used_by' => $usedBy,
            'status' => $coupon->status,
        ]);
    }

    protected function currencyCode($request)
    {
        $currencyCode = 'NGN';
        if($request->country_id) {
            $country = Country::findOrFail($request->country_id);
            $currencyCode = getCurrencyCode($country->sortname);
        }
        return $currencyCode;
    }
}




