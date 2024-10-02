<?php

namespace App\Services\Payment;

use App\Enum\PaymentType;
use App\Mail\CustomerOrderMail;
use App\Mail\SellerOrderMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Product;
use App\Models\User;
use App\Models\UserShippingAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaystackService
{
    public static function handleRecurringCharge($event, $status)
    {
        try {
            DB::transaction(function () use($event, $status) {

                $paymentData = $event['data'];

                $userId = $paymentData['metadata']['user_id'];
                $amount = $paymentData['amount'];
                $formattedAmount = number_format($amount / 100, 2, '.', '');
                $ref = $paymentData['reference'];
                $channel = $paymentData['channel'];
                $currency = $paymentData['currency'];
                $ip_address = $paymentData['ip_address'];
                $paid_at = $paymentData['paid_at'];
                $createdAt = $paymentData['created_at'];
                $transaction_date = $paymentData['paid_at'];
                $payStatus = $paymentData['status'];
                $method = $paymentData['metadata']['payment_method'];
                $planId = $paymentData['metadata']['subscription_plan_id'];
                $authData = $paymentData['authorization'];

                $user = User::with('userSubscription')
                ->findOrFail($userId);

                $payment = Payment::create([
                    'user_id' => $userId,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone,
                    'amount' => $formattedAmount,
                    'reference' => $ref,
                    'channel' => $channel,
                    'currency' => $currency,
                    'ip_address' => $ip_address,
                    'paid_at' => $paid_at,
                    'createdAt' => $createdAt,
                    'transaction_date' => $transaction_date,
                    'status' => $payStatus,
                    'type' => PaymentType::RECURRINGCHARGE,
                ]);

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'data' => $paymentData,
                    'method' => $method,
                    'status' => $status,
                    'type' => PaymentType::RECURRINGCHARGE,
                ]);

                if($user->user_subscribe) {
                    Log::info(`User - {$user} subscription is active`);
                }

                $user->userSubscription()->create([
                    'subscription_plan_id' => $planId,
                    'payment_id' => $payment->id,
                    'plan_start' => now(),
                    'plan_end' => now()->addDays(30),
                    'authorization_data' => $authData,
                    'status' => 'active',
                ]);
                
            });
        } catch (\Exception $e) {
            Log::error('Error in handleRecurringCharge: ' . $e->getMessage());
        }
    }

    public static function handlePaymentSuccess($event, $status)
    {
        try {
            DB::transaction(function () use($event, $status) {

                $paymentData = $event['data'];
                $userId = $paymentData['metadata']['user_id'];
                $items = $paymentData['metadata']['items'];
                $method = $paymentData['metadata']['payment_method'];
                $ref = $paymentData['reference'];
                $amount = $paymentData['amount'];
                $formattedAmount = number_format($amount / 100, 2, '.', '');
                $channel = $paymentData['channel'];
                $currency = $paymentData['currency'];
                $ip_address = $paymentData['ip_address'];
                $paid_at = $paymentData['paid_at'];
                $createdAt = $paymentData['created_at'];
                $transaction_date = $paymentData['paid_at'];
                $payStatus = $paymentData['status'];

                $user = User::findOrFail($userId);
                $address = $paymentData['metadata']['shipping_address'];
                $orderNo = self::orderNo();

                $payment = Payment::create([
                    'user_id' => $userId,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone,
                    'amount' => $formattedAmount,
                    'reference' => $ref,
                    'channel' => $channel,
                    'currency' => $currency,
                    'ip_address' => $ip_address,
                    'paid_at' => $paid_at,
                    'createdAt' => $createdAt,
                    'transaction_date' => $transaction_date,
                    'status' => $payStatus,
                    'type' => PaymentType::USERORDER,
                ]);

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'data' => $paymentData,
                    'method' => $method,
                    'status' => $status,
                    'type' => PaymentType::USERORDER,
                ]);

                $orderedItems = [];
                $seller = [];
                foreach ($items as $item) {

                    $product = Product::find($item['product_id']);

                    $seller = Product::with('user')
                    ->where('id', $item['product_id'])
                    ->first();

                    Order::saveOrder(
                        $user,
                        $payment,
                        $seller->user,
                        $item,
                        $orderNo,
                        $address,
                        $method,
                        $payStatus,
                    );

                    $orderedItems[] = [
                        'product_name' => $product->name,
                        'image' => $product->image,
                        'quantity' => $item['product_quantity'],
                        'price' => $item['total_amount'],
                    ];
                }

                if (!empty($address)) {
                    UserShippingAddress::create([
                        'user_id' => $userId,
                        'first_name' => $address['first_name'],
                        'last_name' => $address['last_name'],
                        'email' => $address['email'],
                        'phone' => $address['phone'],
                        'street_address' => $address['street_address'],
                        'state' => $address['state'],
                        'city' => $address['city'],
                        'zip' => $address['zip'],
                    ]);
                }

                Cart::where('user_id', $userId)->delete();

                self::sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $formattedAmount);
                self::sendSellerOrderEmail($seller->user, $orderedItems, $orderNo, $formattedAmount);
            });
        } catch (\Exception $e) {
            Log::error('Error in handlePaymentSuccess: ' . $e->getMessage());
        }
    }

    private static function orderNo()
    {
        $timestamp = now()->timestamp;
        $randomNumber = mt_rand(100000, 999999);

        $uniqueOrderNumber = 'ORD-' . $timestamp . '-' . $randomNumber;

        while (Order::where('order_no', $uniqueOrderNumber)->exists()) {
            $randomNumber = mt_rand(100000, 999999);
            $uniqueOrderNumber = 'ORD-' . $timestamp . '-' . $randomNumber;
        }

        return $uniqueOrderNumber;
    }

    private static function sendSellerOrderEmail($seller, $order, $orderNo, $totalAmount)
    {
        defer(fn() => send_email($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount)));
    }

    private static function sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $totalAmount)
    {
        defer(fn() => send_email($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount)));
    }
}
