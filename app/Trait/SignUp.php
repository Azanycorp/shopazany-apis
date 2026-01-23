<?php

namespace App\Trait;

use App\Enum\Coupon as EnumCoupon;
use App\Enum\MailingEnum;
use App\Enum\UserType;
use App\Mail\SellerWelcomeMail;
use App\Mail\UserWelcomeMail;
use App\Models\Coupon;
use App\Models\User;

trait SignUp
{
    protected function createUser($request): User
    {
        $code = generateVerificationCode();
        $currencyCode = currencyCodeByCountryId($request->country_id);

        return User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => UserType::CUSTOMER,
            'country' => $request->country_id,
            'state_id' => $request->state_id,
            'default_currency' => $currencyCode,
            'email_verified_at' => null,
            'verification_code' => $code,
            'is_verified' => 0,
            'password' => bcrypt($request->password),
        ]);
    }

    protected function createB2BSeller($request): User
    {
        $code = generateVerificationCode();
        $names = extractNamesFromEmail($request->email);

        $user = User::create([
            'first_name' => $names['first_name'],
            'last_name' => $names['last_name'],
            'email' => $request->email,
            'type' => $request->type,
            'email_verified_at' => null,
            'verification_code' => $code,
            'country' => $request->country_id ?? 160,
            'state_id' => $request->state_id ?? null,
            'is_verified' => 0,
            'info_source' => $request->info_source ?? null,
            'password' => bcrypt($request->password),
        ]);

        if ($request->referrer_code) {
            $affiliate = User::with('wallet')
                ->where([
                    'referrer_code' => $request->referrer_code,
                    'is_affiliate_member' => 1,
                ])
                ->first();

            if (! $affiliate) {
                throw new \Exception('No Affiliate found!');
            }

            $this->handleReferrers($request->referrer_code, $user);
        }

        return $user;
    }

    /**
     * Normalize coupon input.
     * Convert invalid or placeholder values to null.
     *
     * @param  mixed  $coupon
     */
    protected function normalizeCoupon(?string $coupon): ?string
    {
        if (is_null($coupon) || trim(strtolower($coupon)) === 'null' || trim($coupon) === '') {
            return null;
        }

        return $coupon;
    }

    protected function handleReferrers(string $referrerCode, User $user)
    {
        if (! $referrerCode) {
            throw new \InvalidArgumentException('Referrer code is required');
        }

        $referrer = User::with(['wallet', 'referrals'])
            ->where('referrer_code', $referrerCode)
            ->first();

        if (! $referrer || ! $referrer->is_affiliate_member) {
            throw new \Exception('You are not a valid referrer');
        }

        reward_user($referrer, 'referral', 'completed', $user);
    }

    protected function validateCoupon(string $couponCode)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->whereStatus(EnumCoupon::ACTIVE->value)
            ->first();

        if (! $coupon) {
            throw new \Exception('Invalid coupon code or inactive');
        }

        if ($coupon->used) {
            throw new \Exception('Coupon has already been used');
        }

        if ($coupon->expire_at && $coupon->expire_at < now()) {
            throw new \Exception('Coupon has expired');
        }
    }

    protected function validateReferrerCode(string $code)
    {
        $user = User::where('referrer_code', $code)->first();

        if (! $user) {
            throw new \Exception('Invalid referrer code');
        }
    }

    protected function assignCoupon(string $couponCode, User $user)
    {
        $coupon = Coupon::where('code', $couponCode)
            ->whereStatus(EnumCoupon::ACTIVE->value)
            ->lockForUpdate()
            ->first();

        if (! $coupon) {
            return $this->error(null, 'Invalid or expired coupon', 400);
        }

        $coupon->total_used = $coupon->total_used ?? 0;
        $usedBy = $coupon->used_by ?? [];

        $newUserEntry = [
            'user_id' => $user->id,
            'name' => $user->first_name.' '.$user->last_name,
            'email' => $user->email,
        ];

        if ($coupon->type === EnumCoupon::MULTI_USE->value) {
            $coupon->increment('total_used');
            $usedBy[] = $newUserEntry;
        } else {
            $usedBy = [$newUserEntry];
            $coupon->total_used = 1;
        }

        if ($coupon->type === EnumCoupon::ONE_TIME->value || $coupon->total_used >= $coupon->max_use) {
            $coupon->status = EnumCoupon::INACTIVE->value;
        }

        $coupon->update([
            'used' => ($coupon->status === EnumCoupon::INACTIVE->value) ? 1 : 0,
            'used_by' => $usedBy,
            'status' => $coupon->status,
        ]);

        return null;
    }

    protected function sendEmailToUser(User $user): void
    {
        match ($user->type) {
            UserType::CUSTOMER => $this->sendCustomerEmail($user),
            UserType::SELLER,
            UserType::AGRIECOM_SELLER => $this->sendSellerEmail($user),
            default => null,
        };
    }

    /**
     * Send email to b2c customer
     */
    private function sendCustomerEmail(User $user): void
    {
        mailSend(
            MailingEnum::EMAIL_VERIFICATION,
            $user,
            'Email verification',
            UserWelcomeMail::class,
            ['user' => $user]
        );
    }

    /**
     * Send email to b2c seller
     */
    private function sendSellerEmail(User $user): void
    {
        mailSend(
            MailingEnum::EMAIL_VERIFICATION,
            $user,
            'Email verification',
            SellerWelcomeMail::class,
            ['user' => $user]
        );
    }
}
