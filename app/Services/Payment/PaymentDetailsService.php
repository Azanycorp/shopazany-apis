<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Models\User;

class PaymentDetailsService
{
    public static function paystackPayDetails($request): array
    {
        if($request->input('currency') === 'USD') {
            return response()->json([
                'status' => false,
                'message' => "Currrency not available at the moment",
                'data' => null
            ], 400);
        }

        $user = User::findOrFail($request->input('user_id'));

        $amount = $request->input('amount') * 100;
        $userShippingId = $request->input('user_shipping_address_id');
        $address = null;

        if ($userShippingId === 0 && $request->input('shipping_address')) {
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
            $addr = $user->userShippingAddress()->where('id', $userShippingId)->first();
            $address = $addr;
        }

        $callbackUrl = $request->input('payment_redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid callback URL'], 400);
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
            return response()->json([
                'status' => false,
                'message' => "Currrency not available at the moment",
                'data' => null
            ], 400);
        }

        User::findOrFail($request->input('user_id'));

        $amount = $request->input('amount') * 100;
        $callbackUrl = $request->input('payment_redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid callback URL'], 400);
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
}


