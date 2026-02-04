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
            'id' => (int) $this->resource->id,
            'uuid' => (string) $this->resource->uuid,
            'first_name' => (string) $this->resource->first_name,
            'last_name' => (string) $this->resource->last_name,
            'middlename' => (string) $this->resource->middlename,
            'email' => (string) $this->resource->email,
            'business_name' => (string) $this->resource->company_name,
            'address' => (string) $this->resource->address,
            'city' => (string) $this->resource->city,
            'postal_code' => (string) $this->resource->postal_code,
            'phone' => (string) $this->resource->phone,
            'country_id' => (string) $this->resource->country,
            'state_id' => (string) $this->resource->state_id,
            'default_currency' => (string) $this->resource->default_currency,
            'referrer_code' => (string) $this->resource->referrer_code,
            'referrer_link' => $this->resource->referrer_link,
            'date_of_birth' => (string) $this->resource->date_of_birth,
            'is_verified' => (bool) $this->resource->is_verified,
            'income_type' => (string) $this->resource->income_type,
            'image' => (string) $this->resource->image,
            'type' => (string) $this->resource->type,
            'is_affiliate_member' => (bool) $this->resource->is_affiliate_member,
            'two_factor_enabled' => (bool) $this->resource->two_factor_enabled,
            'is_biometric_enabled' => (bool) $this->resource->biometric_enabled,
            'hear_about_us' => (string) $this->resource->hear_about_us,
            'status' => (string) $this->resource->status,
            'wallet' => (object) [
                'available_balance' => $this->resource->wallet?->balance,
                'total_income' => 0,
                'total_withdrawal' => 0,
                'total_points' => $this->resource->wallet?->reward_point,
                'points_cleared' => $this->resource->wallet?->points_cleared,
            ],
            'no_of_referrals' => $this->resource->referrals_count,
            'bank_account' => (object) [
                'account_name' => $this->resource->bankAccount?->account_name,
                'bank_name' => $this->resource->bankAccount?->bank_name,
                'account_number' => $this->resource->bankAccount?->account_number,
            ],
            'business_info' => (object) [
                'business_location' => $this->resource->userbusinessinfo?->business_location,
                'business_type' => $this->resource->userbusinessinfo?->business_type,
                'identity_type' => $this->resource->userbusinessinfo?->identity_type,
                'file' => $this->resource->userbusinessinfo?->file,
                'status' => $this->resource->userbusinessinfo?->status,
            ],
            'shipping_address' => $this->resource->userShippingAddress ? $this->resource->userShippingAddress->map(fn ($addr) => [
                'id' => $addr?->id,
                'first_name' => $addr?->first_name,
                'last_name' => $addr?->last_name,
                'email' => $addr?->email,
                'phone' => $addr?->phone,
                'street_address' => $addr?->street_address,
                'state' => $addr?->state,
                'city' => $addr?->city,
                'zip' => $addr?->zip,
            ])->toArray() : [],
            'subscribed' => $this->resource->is_subscribed,
            'user_subscription_plan' => (object) [
                'id' => (int) $this->resource->subscription_plan?->id,
                'subscription_plan_id' => (int) $this->resource->subscription_plan?->subscriptionPlan?->id,
                'plan' => (string) $this->resource->subscription_plan?->subscriptionPlan?->title,
                'plan_start' => (string) $this->resource->subscription_plan?->plan_start,
                'plan_end' => (string) $this->resource->subscription_plan?->plan_end,
                'expired_at' => (string) $this->resource->subscription_plan?->expired_at,
                'status' => (string) $this->resource->subscription_plan?->status,
            ],
            'rewards' => getRewards($this->resource->country),
            'user_rewards' => userRewards($this->resource->id),
        ];
    }
}
