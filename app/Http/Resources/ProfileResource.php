<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'uuid' => (string) $this->uuid,
            'first_name' => (string) $this->first_name,
            'last_name' => (string) $this->last_name,
            'middlename' => (string) $this->middlename,
            'email' => (string) $this->email,
            'business_name' => (string) $this->company_name,
            'address' => (string) $this->address,
            'city' => (string) $this->city,
            'postal_code' => (string) $this->postal_code,
            'phone' => (string) $this->phone,
            'country_id' => (string) $this->country,
            'state_id' => (string) $this->state_id,
            'default_currency' => (string) $this->default_currency,
            'referrer_code' => (string) $this->referrer_code,
            'referrer_link' => $this->referrer_link,
            'date_of_birth' => (string) $this->date_of_birth,
            'is_verified' => (bool) $this->is_verified,
            'income_type' => (string) $this->income_type,
            'image' => (string) $this->image,
            'type' => (string) $this->type,
            'is_affiliate_member' => (bool) $this->is_affiliate_member,
            'two_factor_enabled' => (bool) $this->two_factor_enabled,
            'is_biometric_enabled' => (bool) $this->biometric_enabled,
            'status' => (string) $this->status,
            'wallet' => (object) [
                'available_balance' => $this->wallet?->balance,
                'total_income' => 0,
                'total_withdrawal' => 0,
                'total_points' => $this->wallet?->reward_point,
                'points_cleared' => $this->wallet?->points_cleared,
            ],
            'no_of_referrals' => $this->referrals_count,
            'bank_account' => (object) [
                'account_name' => $this->bankAccount?->account_name,
                'bank_name' => $this->bankAccount?->bank_name,
                'account_number' => $this->bankAccount?->account_number,
            ],
            'business_info' => (object) [
                'business_location' => $this->userbusinessinfo?->business_location,
                'business_type' => $this->userbusinessinfo?->business_type,
                'identity_type' => $this->userbusinessinfo?->identity_type,
                'file' => $this->userbusinessinfo?->file,
                'status' => $this->userbusinessinfo?->status,
            ],
            'shipping_address' => $this->userShippingAddress ? $this->userShippingAddress->map(function ($addr): array {
                return [
                    'id' => $addr?->id,
                    'street_address' => $addr?->street_address,
                    'state' => $addr?->state,
                    'city' => $addr?->city,
                    'zip' => $addr?->zip,
                ];
            })->toArray() : [],
            'subscribed' => $this->is_subscribed,
            'user_subscription_plan' => (object) [
                'id' => (int) $this->subscription_plan?->id,
                'subscription_plan_id' => (int) $this->subscription_plan?->subscriptionPlan?->id,
                'plan' => (string) $this->subscription_plan?->subscriptionPlan?->title,
                'plan_start' => (string) $this->subscription_plan?->plan_start,
                'plan_end' => (string) $this->subscription_plan?->plan_end,
                'expired_at' => (string) $this->subscription_plan?->expired_at,
                'status' => (string) $this->subscription_plan?->status,
            ],
            'rewards' => getRewards($this->country),
            'user_rewards' => userRewards($this->id),
        ];
    }
}
