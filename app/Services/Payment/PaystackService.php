<?php

namespace App\Services\Payment;

use App\Models\Rfq;
use App\Models\Cart;
use App\Models\User;
use App\Enum\UserLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\B2bOrder;
use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Models\B2BProduct;
use App\Models\UserWallet;
use App\Mail\B2BOrderEmail;
use Illuminate\Support\Str;
use App\Mail\SellerOrderMail;
use App\Models\Configuration;
use App\Models\ShippingAgent;
use App\Actions\UserLogAction;
use App\Enum\SubscriptionType;
use App\Mail\CustomerOrderMail;
use App\Actions\PaymentLogAction;
use Illuminate\Support\Facades\DB;
use App\Models\UserShippingAddress;
use Illuminate\Support\Facades\Log;
use App\Models\BuyerShippingAddress;
use App\Http\Resources\B2BBuyerShippingAddressResource;
use App\Services\SubscriptionService;

class PaystackService
{
    public static function handleRecurringCharge($event, $status): void
    {
        try {
            DB::transaction(function () use ($event, $status): void {

                $paymentData = $event['data'];

                $userId = $paymentData['metadata']['user_id'];
                $referrerId = $paymentData['metadata']['referrer_id'];
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

                $user = User::findOrFail($userId);
                $referrer = User::with(['wallet'])->find($referrerId);

                $activeSubscription = $user->subscription_plan;
                if ($activeSubscription) {
                    $activeSubscription->update([
                        'status' => SubscriptionType::EXPIRED,
                        'expired_at' => now(),
                    ]);
                }

                $data = (object)[
                    'user_id' => $userId,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
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
                ];

                $payment = (new PaymentLogAction($data, $paymentData, $method, $status))->execute();

                $user->userSubscriptions()->create([
                    'subscription_plan_id' => $planId,
                    'payment_id' => $payment->id,
                    'plan_start' => now(),
                    'plan_end' => now()->addDays(30),
                    'authorization_data' => $authData,
                    'status' => SubscriptionType::ACTIVE,
                    'expired_at' => null,
                ]);

                SubscriptionService::creditAffiliate($referrer, $formattedAmount);
            });
        } catch (\Exception $e) {
            Log::error('Error in handleRecurringCharge: ' . $e->getMessage());
        }
    }

    public static function handlePaymentSuccess($event, $status): void
    {
        $paymentData = null;
        $user = null;

        try {
            DB::transaction(function () use ($event, $status): void {

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
                $userShippingId = $paymentData['metadata']['user_shipping_address_id'];
                $orderNo = self::orderNo();

                $data = (object)[
                    'user_id' => $userId,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
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
                ];

                $payment = (new PaymentLogAction($data, $paymentData, $method, $status))->execute();

                $orderedItems = [];
                foreach ($items as $item) {
                    $product = Product::with('user')
                        ->findOrFail($item['product_id']);

                    Order::saveOrder(
                        $user,
                        $payment,
                        $product->user,
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

                    $product->decrement('current_stock_quantity', $item['product_quantity']);
                }

                if ($userShippingId === 0) {
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
                self::sendSellerOrderEmail($product->user, $orderedItems, $orderNo, $formattedAmount);

                (new UserLogAction(
                    request(),
                    UserLog::PAYMENT,
                    "Payment successful",
                    json_encode($paymentData),
                    $user
                ))->run();
            });
        } catch (\Exception $e) {
            $msg = 'Error in handlePaymentSuccess: ' . $e->getMessage();

            (new UserLogAction(
                request(),
                UserLog::PAYMENT,
                $msg,
                json_encode($paymentData),
                $user
            ))->run();

            Log::error('Error in handlePaymentSuccess: ' . $e->getMessage());
        }
    }

    public static function handleB2BPaymentSuccess($event, $status): void
    {
        try {
            DB::transaction(function () use ($event, $status): void {
                $paymentData = $event['data'];
                $userId = $paymentData['metadata']['user_id'];
                $rfqId = $paymentData['metadata']['rfq_id'];
                $shipping_address_id = $paymentData['metadata']['shipping_address_id'];
                $shipping_agent_id = $paymentData['metadata']['shipping_agent_id'];
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

                $orderNo = self::orderNo();

                $data = (object)[
                    'user_id' => $userId,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'amount' => $formattedAmount,
                    'reference' => $ref,
                    'channel' => $channel,
                    'currency' => $currency,
                    'ip_address' => $ip_address,
                    'paid_at' => $paid_at,
                    'createdAt' => $createdAt,
                    'transaction_date' => $transaction_date,
                    'status' => $payStatus,
                    'type' => PaymentType::B2BUSERORDER,
                ];

                (new PaymentLogAction($data, $paymentData, $method, $status))->execute();

                if ($shipping_agent_id) {
                    $shipping_agent =  ShippingAgent::findOrFail(2);
                }

                $rfq = Rfq::findOrFail($rfqId);
                $seller = User::findOrFail($rfq->seller_id);
                $product = B2BProduct::findOrFail($rfq->product_id);
                $shipping_address = BuyerShippingAddress::with(['state', 'country'])->findOrFail($shipping_address_id);
                $address = new B2BBuyerShippingAddressResource($shipping_address);

                B2bOrder::create([
                    'buyer_id' => $userId,
                    'seller_id' => $rfq->seller_id,
                    'product_id' => $rfq->product_id,
                    'product_quantity' => $rfq->product_quantity,
                    'order_no' => $orderNo,
                    'product_data' => $product,
                    'shipping_agent' => $shipping_agent_id ? $shipping_agent->name : 'DHL',
                    'shipping_address' => $address,
                    'total_amount' => $amount,
                    'payment_method' => $method,
                    'payment_status' => OrderStatus::PAID,
                    'status' => OrderStatus::PENDING,
                ]);

                $orderedItems = [
                    'product_name' => $product->name,
                    'image' => $product->front_image,
                    'quantity' => $rfq->product_quantity,
                    'price' => $rfq->total_amount,
                    'buyer_name' => $user->first_name . ' ' . $user->last_name,
                    'order_number' => $orderNo,
                ];
                $product->quantity -= $rfq->product_quantity;
                $product->sold += $rfq->product_quantity;
                $product->save();

                $config = Configuration::first();

                if ($config) {
                    $sellerPerc = $config->seller_perc ?? 0;
                    $credit = ($sellerPerc / 100) * $amount;

                    $wallet = UserWallet::firstOrNew(['seller_id' => $seller->id]);
                    $wallet->master_wallet = ($wallet->master_wallet ?? 0) + $credit;
                    $wallet->save();
                }

                $rfq->update([
                    'payment_status' => OrderStatus::PAID,
                    'status' => OrderStatus::COMPLETED
                ]);

                $type = MailingEnum::ORDER_EMAIL;
                $subject = "B2B Order Confirmation";
                $mail_class = "App\Mail\B2BOrderEmail";
                mailSend($type, $user, $subject, $mail_class,'orderedItems');

                (new UserLogAction(
                    request(),
                    UserLog::PAYMENT,
                    "Payment successful",
                    json_encode($paymentData),
                    $user
                ))->run();
            });
        } catch (\Exception $e) {
            Log::error('Error in handlePaymentSuccess: ' . $e->getMessage());
        }
    }

    private static function orderNo(): string
    {
        do {
            $uniqueOrderNumber = 'ORD-' . now()->timestamp . '-' . Str::random(8);
        } while (Order::where('order_no', $uniqueOrderNumber)->exists());

        return $uniqueOrderNumber;
    }

    private static function sendSellerOrderEmail($seller, $order, $orderNo, string $totalAmount): void
    {
        defer(fn() => send_email($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount)));
    }

    private static function sendOrderConfirmationEmail($user, $orderedItems, $orderNo, string $totalAmount): void
    {
        defer(fn() => send_email($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount)));
    }
}
