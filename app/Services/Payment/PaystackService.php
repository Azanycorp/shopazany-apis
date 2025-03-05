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
use App\Enum\UserType;
use App\Enum\WithdrawalStatus;
use Illuminate\Support\Facades\DB;
use App\Models\UserShippingAddress;
use Illuminate\Support\Facades\Log;
use App\Models\BuyerShippingAddress;
use App\Http\Resources\B2BBuyerShippingAddressResource;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalNotification;
use App\Services\Curl\PostCurl;
use App\Services\SubscriptionService;
use Carbon\Carbon;

class PaystackService
{
    public static function handleRecurringCharge($event, $status): void
    {
        try {
            DB::transaction(function () use ($event, $status): void {

                $paymentData = $event['data'];
                $ref = $paymentData['reference'];
                $userId = $paymentData['metadata']['user_id'];
                $amount = $paymentData['amount'];
                $formattedAmount = number_format($amount / 100, 2, '.', '');
                $channel = $paymentData['channel'];
                $paid_at = Carbon::parse($paymentData['paid_at']);

                if (Payment::where('reference', $ref)->exists()) {
                    Log::info("Duplicate payment detected: {$ref}, skipping processing.");
                    return;
                }

                $duplicatePayment = Payment::where('user_id', $userId)
                    ->where('amount', $formattedAmount)
                    ->where('channel', $channel)
                    ->whereBetween('created_at', [$paid_at->subMinutes(5), $paid_at->addMinutes(5)])
                    ->exists();

                if ($duplicatePayment) {
                    Log::info("Duplicate payment detected for user {$userId}, skipping processing.");
                    return;
                }

                $referrerId = $paymentData['metadata']['referrer_id'];
                $currency = $paymentData['currency'];
                $ip_address = $paymentData['ip_address'];
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

                SubscriptionService::creditAffiliate($referrer, $formattedAmount, $user);
            });
        } catch (\Exception $e) {
            Log::error('Error in handleRecurringCharge: ' . $e->getMessage());
        }
    }

    public static function handlePaymentSuccess($event, $status): void
    {
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
                    $product = Product::with(['user.wallet', 'shopCountry'])
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
                        'currency' => $user->default_currency,
                    ];

                    $product->decrement('current_stock_quantity', $item['product_quantity']);

                    if ($product->user) {
                        $wallet = Wallet::firstOrCreate(
                            ['user_id' => $product->user->id],
                            ['balance' => 0]
                        );

                        $amount = currencyConvert(
                            $user->default_currency,
                            $item['total_amount'],
                            $product->shopCountry->currency,
                        );

                        $wallet->increment('balance', $amount);
                    }
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

                if ($user->type === UserType::CUSTOMER) {
                    reward_user($user, 'purchase_item', 'completed');
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
            Log::error('Error in handlePaymentSuccess: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            throw $e;
        }
    }

    public static function handleB2BPaymentSuccess($event, $status): void
    {
        try {
            DB::transaction(function () use ($event, $status): void {
                $paymentData = $event['data'];

                $metadata = $paymentData['metadata'] ?? [];

                $userId = $metadata['user_id'] ?? null;
                $rfqId = $metadata['rfq_id'] ?? null;
                $centerId = $metadata['center_id'] ?? null;
                $shipping_address_id = $metadata['shipping_address_id'] ?? null;
                $shipping_agent_id = $metadata['shipping_agent_id'] ?? null;
                $method = $metadata['payment_method'] ?? null;
                $ref = $paymentData['reference'] ?? null;
                $amount = $paymentData['amount'] ?? 0;

                $formattedAmount = number_format($amount / 100, 2, '.', '');
                $channel = $paymentData['channel'] ?? null;
                $currency = $paymentData['currency'] ?? null;
                $ip_address = $paymentData['ip_address'] ?? null;
                $paid_at = $paymentData['paid_at'] ?? null;
                $createdAt = $paymentData['created_at'] ?? null;
                $transaction_date = $paymentData['paid_at'] ?? null;
                $payStatus = $paymentData['status'] ?? null;

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

                $shipping_agent = null;
                if ($shipping_agent_id) {
                    $shipping_agent = ShippingAgent::findOrFail($shipping_agent_id);
                }

                $rfq = Rfq::findOrFail($rfqId);
                $seller = User::findOrFail($rfq->seller_id);
                $product = B2BProduct::findOrFail($rfq->product_id);
                $shipping_address = null;
                if ($shipping_address_id) {
                    $shipping_address = BuyerShippingAddress::with(['state', 'country'])->find($shipping_address_id);
                }
                $address = $shipping_address ? new B2BBuyerShippingAddressResource($shipping_address) : null;

                B2bOrder::create([
                    'buyer_id' => $userId,
                    'centre_id' => $centerId ?? null,
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

                $product->availability_quantity -= $rfq->product_quantity;
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
                $orderedItems = [
                    'product_name' => $product->name,
                    'image' => $product->front_image,
                    'quantity' => $rfq->product_quantity,
                    'price' => $rfq->total_amount,
                    'buyer_name' => $user->first_name . ' ' . $user->last_name,
                    'order_number' => $orderNo,
                ];
                $type = MailingEnum::ORDER_EMAIL;
                $subject = "B2B Order Confirmation";
                $mail_class = B2BOrderEmail::class;
                mailSend($type, $user, $subject, $mail_class, $orderedItems);

                (new UserLogAction(
                    request(),
                    UserLog::PAYMENT,
                    "Payment successful",
                    json_encode($paymentData),
                    $user
                ))->run();
            });
        } catch (\Exception $e) {
            Log::error('Error in handlePaymentSuccess: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            throw $e;
        }
    }

    public static function handleTransferSuccess($event): void
    {
        $reference = $event['reference'];
        $withdrawal = WithdrawalRequest::with('user')
            ->where('reference', $reference)
            ->first();

        if (!$withdrawal) {
            Log::error("Transfer success: No matching withdrawal found for reference: {$reference}");
            return;
        }

        DB::beginTransaction();
        try {
            $withdrawal->update(['status' => WithdrawalStatus::COMPLETED]);

            $user = $withdrawal->user;
            $user->notify(new WithdrawalNotification($withdrawal, 'completed'));

            Log::info("Transfer successful for withdrawal ID {$withdrawal->id} - Reference: {$reference}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing transfer success: " . $e->getMessage());
            throw $e;
        }
    }

    public static function handleTransferFailed($event)
    {
        $reference = $event['reference'];
        $withdrawal = WithdrawalRequest::with('user')
            ->where('reference', $reference)
            ->first();

        if (!$withdrawal) {
            Log::error("Transfer success: No matching withdrawal found for reference: {$reference}");
            return;
        }

        DB::beginTransaction();
        try {
            $withdrawal->update(['status' => WithdrawalStatus::FAILED]);

            $user = $withdrawal->user;
            $user->notify(new WithdrawalNotification($withdrawal, 'failed'));

            Log::info("Transfer failed for withdrawal ID {$withdrawal->id} - Reference: {$reference}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error processing transfer success: " . $e->getMessage());
            throw $e;
        }
    }

    public static function createRecipient($fields, $method)
    {
        $url = "https://api.paystack.co/transferrecipient";
        $token = config('paystack.secretKey');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
        $data = (new PostCurl($url, $headers, $fields))->execute();
        self::logTransfer($data, $method);
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

    private static function logTransfer($data, $method)
    {
        $method->update([
            'recipient_code' => $data['recipient_code'],
            'data' => $data,
        ]);
    }
}
