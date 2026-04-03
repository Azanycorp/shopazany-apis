<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Enum\UserStatus;
use App\Enum\UserTypes;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Collection;

class PaymentDetailsService
{
    public static function paystackPayDetails($request): array
    {
        $currency = $request->input('currency');
        $userId = (int) $request->input('user_id');
        $amount = (int) $request->input('amount') * 100;
        $userShippingId = (int) $request->input('user_shipping_address_id');
        $callbackUrl = $request->input('payment_redirect_url');
        $items = $request->input('items', []);
        $productIds = (new Collection($items))->pluck('product_id')->toArray();

        if ($currency === 'USD') {
            return [
                'status' => false,
                'message' => 'Currrency not available at the moment',
                'data' => null,
            ];
        }

        if (! filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return [
                'status' => false,
                'message' => 'Invalid callback URL',
                'data' => null,
            ];
        }

        $user = User::findOrFail($userId);

        if ($userShippingId === 0 && $request->filled('shipping_address')) {
            $shippingAddress = $request->input('shipping_address');

            $address = (object) [
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
            $address = $user->userShippingAddress()->find($userShippingId);

            if (! $address) {
                return [
                    'status' => false,
                    'message' => 'Shipping address not found',
                    'data' => null,
                ];
            }
        }

        $products = Product::whereIn('id', $productIds)->get();

        if ($products->count() !== count($productIds)) {
            return [
                'status' => false,
                'message' => 'One or more products not found',
                'data' => null,
            ];
        }

        foreach ($items as $item) {
            $product = $products->firstWhere('id', $item['product_id']);

            if ($item['product_quantity'] > $product->current_stock_quantity) {
                return [
                    'status' => false,
                    'message' => "Only {$product->current_stock_quantity} unit(s) of {$product->name} are available",
                    'data' => null,
                ];
            }
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
            'callback_url' => $request->input('payment_redirect_url'),
        ];
    }

    public static function b2bPaystackPayDetails($request): array
    {
        if ($request->input('currency') === 'USD') {
            return [
                'status' => false,
                'message' => 'Currrency not available at the moment',
                'data' => null,
            ];
        }

        User::findOrFail($request->input('user_id'));

        $amount = $request->input('amount') * 100;
        $callbackUrl = $request->input('payment_redirect_url');
        if (! filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
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
            'callback_url' => $request->input('payment_redirect_url'),
        ];
    }

    public static function paystackSubcriptionPayDetails($request): array
    {
        $callbackUrl = $request->input('redirect_url');

        $user = User::with([
            'referrer' => fn ($q) => $q->with('wallet'),
            'userSubscriptions' => fn ($q) => $q->where('status', UserStatus::ACTIVE),
        ])->findOrFail($request->user_id);

        $error = self::validateSubscriptionRequest($request, $user, $callbackUrl);
        if ($error) {
            return ['error' => $error];
        }

        return [
            'email' => $request->input('email'),
            'amount' => $request->input('amount') * 100,
            'currency' => 'NGN',
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'referrer_id' => $user->referrer->first()?->id,
                'subscription_plan_id' => $request->input('subscription_plan_id'),
                'payment_method' => PaymentType::PAYSTACK,
                'payment_type' => PaymentType::RECURRINGCHARGE,
            ]),
            'callback_url' => $callbackUrl,
        ];
    }

    public static function authorizeNetSubcriptionPayDetails($request): array
    {
        $amount = $request->input('amount');

        $user = User::with([
            'referrer' => function ($query): void {
                $query->with('wallet');
            },
            'userSubscriptions' => function ($query): void {
                $query->where('status', UserStatus::ACTIVE);
            },
        ])->findOrFail($request->user_id);

        if (! in_array($user->type, [UserTypes::SELLER->value, UserTypes::B2B_SELLER->value, UserTypes::AGRIECOM_SELLER->value])) {
            return ['error' => 'You are not allowed to subscribe to a plan'];
        }

        if ($user->is_subscribed) {
            $currentPlan = $user->subscription_plan->subscriptionPlan;
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

    private static function validateSubscriptionRequest($request, User $user, string $callbackUrl): ?string
    {
        if (! filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return 'Invalid callback URL';
        }

        $allowedTypes = [
            UserTypes::SELLER->value,
            UserTypes::B2B_SELLER->value,
            UserTypes::AGRIECOM_SELLER->value,
        ];

        if (! in_array($user->type, $allowedTypes)) {
            return 'You are not allowed to subscribe to a plan';
        }

        if ($user->is_subscribed) {
            $currentPlan = $user->activeSubscription()->subscriptionPlan;
            $newPlan = SubscriptionPlan::findOrFail($request->input('subscription_plan_id'));

            if ($newPlan->tier < $currentPlan->tier) {
                return 'You cannot downgrade your subscription plan';
            }

            if ($newPlan->id === $currentPlan->id) {
                return 'You are already subscribed to this plan';
            }
        }

        return null;
    }
}
