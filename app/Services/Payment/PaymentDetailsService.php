<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Models\SubscriptionPlan;
use App\Models\User;

class PaymentDetailsService
{
    public static function paystackPayDetails($request): array
    {
        if($request->input('currency') === 'USD') {
            return [
                'status' => false,
                'message' => "Currrency not available at the moment",
                'data' => null
            ];
        }

        $user = User::findOrFail($request->input('user_id'));

        $amount = $request->input('amount') * 100;
        $userShippingId = $request->input('user_shipping_address_id');
        $address = null;

        if ($userShippingId === 0 && $request->input('shipping_address')) {
            $shippingAddress = $request->input('shipping_address');
            $address = (object)[
                'first_name' => $shippingAddress['first_name'] ?? '',
                'last_name' => $shippingAddress['last_name'] ?? '',
                'email' => $shippingAddress['email'] ?? '',
                'phone' => $shippingAddress['phone'] ?? '',
                'street_address' => $shippingAddress['street_address'] ?? '',
                'state' => $shippingAddress['state'] ?? '',
                'city' => $shippingAddress['city'] ?? '',
                'zip' => $shippingAddress['zip'] ?? '',
            ];
        } else {
            $address = $user->userShippingAddress()->where('id', $userShippingId)->first();
        }

        $callbackUrl = $request->input('payment_redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid callback URL'];
        }

        return [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => $request->input('currency'),
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'shipping_address' => $address,
                'user_shipping_address_id' => $userShippingId,
                'items' => $request->input('items'),
                'payment_method' => $request->input('payment_method'),
                'payment_type' => PaymentType::USERORDER,
            ]),
            'callback_url' => $request->input('payment_redirect_url')
        ];
    }

    public static function b2bPaystackPayDetails($request): array
    {
        if($request->input('currency') === 'USD') {
            return [
                'status' => false,
                'message' => "Currrency not available at the moment",
                'data' => null
            ];
        }

        User::findOrFail($request->input('user_id'));

        $amount = $request->input('amount') * 100;
        $callbackUrl = $request->input('payment_redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid callback URL'];
        }

        return [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => $request->input('currency'),
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'rfq_id' => $request->input('rfq_id'),
                'payment_method' => $request->input('payment_method'),
                'payment_type' => PaymentType::B2BUSERORDER,
            ]),
            'callback_url' => $request->input('payment_redirect_url')
        ];
    }

    public static function paystackSubcriptionPayDetails($request): array
    {
        $amount = $request->input('amount') * 100;

        $callbackUrl = $request->input('redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid callback URL'];
        }

        $user = User::with([
            'referrer' => function ($query) {
                $query->with('wallet');
            },
            'userSubscriptions' => function ($query) {
                $query->where('status', UserStatus::ACTIVE);
            }
        ])->findOrFail($request->user_id);

        if(!in_array($user->type, [UserType::SELLER, UserType::B2B_SELLER])) {
            return ['error' => 'You are not allowed to subscribe to a plan'];
        }

        if($user->is_subscribed) {
            $currentPlan = $user?->subscription_plan?->subscriptionPlan;
            $newPlan = SubscriptionPlan::findOrFail($request->input('subscription_plan_id'));

            if ($newPlan->tier < $currentPlan->tier) {
                return ['error' => 'You cannot downgrade your subscription plan'];
            }
            if ($newPlan->id == $currentPlan->id) {
                return ['error' => 'You are already subscribed to this plan'];
            }
        }

        return [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => 'NGN',
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'referrer_id' => $user->referrer->first()?->id,
                'subscription_plan_id' => $request->input('subscription_plan_id'),
                'payment_method' => PaymentType::PAYSTACK,
                'payment_type' => PaymentType::RECURRINGCHARGE,
            ]),
            'callback_url' => $callbackUrl
        ];

    }

    public static function authorizeNetSubcriptionPayDetails($request): array
    {
        $amount = $request->input('amount') * 100;

        $user = User::with([
            'referrer' => function ($query) {
                $query->with('wallet');
            },
            'userSubscriptions' => function ($query) {
                $query->where('status', UserStatus::ACTIVE);
            }
        ])->findOrFail($request->user_id);

        if(!in_array($user->type, [UserType::SELLER, UserType::B2B_SELLER])) {
            return ['error' => 'You are not allowed to subscribe to a plan'];
        }

        if($user->is_subscribed) {
            $currentPlan = $user?->subscription_plan?->subscriptionPlan;
            $newPlan = SubscriptionPlan::findOrFail($request->input('subscription_plan_id'));

            if ($newPlan->tier < $currentPlan->tier) {
                return ['error' => 'You cannot downgrade your subscription plan'];
            }
            if ($newPlan->id == $currentPlan->id) {
                return ['error' => 'You are already subscribed to this plan'];
            }
        }

        return [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => 'USD',
            'subscription_plan_id' => $request->input('subscription_plan_id'),
            'referrer_id' => $user->referrer->first()?->id,
            'payment_method' => PaymentType::AUTHORIZE,
            'card_number' => $request->input('card_number'),
            'expiration_date' => $request->input('expiration_date'),
            'card_code' => $request->input('card_code'),
        ];
    }
}


