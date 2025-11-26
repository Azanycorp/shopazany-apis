<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgriEcomSellerProfileResource extends JsonResource
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
            'status' => (string) $this->resource->status,
            'wallet' => $this->resource->whenLoaded('wallet', fn () => new WalletResource($this->resource->wallet)),
            'no_of_referrals' => $this->resource->referrals_count,
            'bank_account' => $this->resource->whenLoaded('bankAccount', fn () => new BankAccountResource($this->resource->bankAccount)),
            'business_info' => $this->resource->whenLoaded('userbusinessinfo', fn () => new BusinessInfoResource($this->resource->userbusinessinfo)),
            'shipping_address' => $this->resource->whenLoaded('userShippingAddress', fn () => ShippingAddressResource::collection($this->resource->userShippingAddress)),
            'subscribed' => $this->resource->is_subscribed,
            'user_subscription_plan' => (object) [
                'id' => (int) $this->resource->subscription_plan?->resource->id,
                'subscription_plan_id' => (int) $this->resource->subscription_plan?->resource->subscriptionPlan?->resource->id,
                'plan' => (string) $this->resource->subscription_plan?->resource->subscriptionPlan?->resource->title,
                'plan_start' => (string) $this->resource->subscription_plan?->resource->plan_start,
                'plan_end' => (string) $this->resource->subscription_plan?->resource->plan_end,
                'expired_at' => (string) $this->resource->subscription_plan?->resource->expired_at,
                'status' => (string) $this->resource->subscription_plan?->resource->status,
            ],
            'rewards' => getRewards($this->resource->country),
            'user_rewards' => userRewards($this->resource->id),
            'products' => $this->resource->whenLoaded('products', fn () => SellerProductResource::collection($this->resource->products)),
        ];
    }
}
