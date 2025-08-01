<?php

namespace App\Services\Payment;

use App\Actions\PaymentLogAction;
use App\Actions\UserLogAction;
use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\SubscriptionType;
use App\Enum\UserLog;
use App\Enum\UserType;
use App\Enum\WithdrawalStatus;
use App\Http\Resources\B2BBuyerShippingAddressResource;
use App\Mail\B2BOrderEmail;
use App\Mail\CustomerOrderMail;
use App\Mail\SellerOrderMail;
use App\Models\Action;
use App\Models\B2bOrder;
use App\Models\B2BProduct;
use App\Models\BuyerShippingAddress;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Rfq;
use App\Models\ShippingAgent;
use App\Models\User;
use App\Models\UserShippingAddress;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Notifications\WithdrawalNotification;
use App\Services\Curl\PostCurl;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

                $data = (object) [
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
                    'subscription_type' => PaymentType::PAYSTACK,
                    'authorization_data' => $authData,
                    'status' => SubscriptionType::ACTIVE,
                    'expired_at' => null,
                ]);

                SubscriptionService::creditAffiliate($referrer, $formattedAmount, $currency);
            });
        } catch (\Exception $e) {
            Log::error('Error in handleRecurringCharge: '.$e->getMessage());
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

                $user = User::with('wallet')->findOrFail($userId);
                $address = $paymentData['metadata']['shipping_address'];
                $userShippingId = $paymentData['metadata']['user_shipping_address_id'];
                $orderNo = self::orderNo();

                $data = (object) [
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
                $totalAmount = 0;
                $product = null;

                foreach ($items as $item) {
                    $product = Product::with(['user.wallet', 'shopCountry'])
                        ->findOrFail($item['product_id']);

                    $convertedPrice = currencyConvert(
                        $user->default_currency,
                        $item['total_amount'],
                        $product->shopCountry?->currency
                    );
                    $totalAmount += $convertedPrice;
                }

                $order = Order::create([
                    'user_id' => $user->id,
                    'payment_id' => $payment->id,
                    'order_no' => $orderNo,
                    'total_amount' => $totalAmount,
                    'payment_method' => $method,
                    'payment_status' => $payStatus,
                    'order_date' => now(),
                    'shipping_address' => $address,
                    'country_id' => $user->country ?? 160,
                    'status' => OrderStatus::PENDING,
                ]);

                $msg = 'Your order has been placed successfully.';
                logOrderActivity($order->id, $msg, OrderStatus::PENDING);

                $orderedItems = [];

                foreach ($items as $item) {
                    $orderedItems[] = self::attachProductToOrder($item, $user, $order);
                }

                if ($userShippingId === 0) {
                    UserShippingAddress::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'street_address' => $address['street_address'],
                        ],
                        [
                            'first_name' => $address['first_name'],
                            'last_name' => $address['last_name'],
                            'email' => $address['email'],
                            'phone' => $address['phone'],
                            'state' => $address['state'],
                            'city' => $address['city'],
                            'zip' => $address['zip'],
                        ]
                    );
                }

                if ($user->type === UserType::CUSTOMER) {
                    $actionSlug = Action::whereIn('name', ['Purchase item', 'Purchase an item'])
                        ->orWhere('slug', 'purchase_an_item')
                        ->value('slug');

                    reward_user($user, $actionSlug, 'completed');
                }

                Cart::where('user_id', $userId)->delete();

                self::sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $formattedAmount);
                self::sendSellerOrderEmail($product?->user, $orderedItems, $orderNo, $formattedAmount);

                (new UserLogAction(
                    request(),
                    UserLog::PAYMENT,
                    'Payment successful',
                    json_encode($paymentData),
                    $user
                ))->run();
            });
        } catch (\Exception $e) {
            Log::error('Error in handlePaymentSuccess: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
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

                $data = (object) [
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

                $product->availability_quantity -= $rfq->product_quantity;
                $product->sold += $rfq->product_quantity;
                $seller_amount = currencyConvert(
                    $user->default_currency,
                    $formattedAmount,
                    $product->shopCountry->currency ?? 'USD',
                );
                $product->save();

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
                    'total_amount' => $formattedAmount,
                    'payment_method' => $method,
                    'payment_status' => OrderStatus::PAID,
                    'status' => OrderStatus::PENDING,
                ]);

                $wallet = UserWallet::firstOrCreate(
                    ['seller_id' => $seller->id],
                    ['master_wallet' => 0]
                );
                $wallet->increment('master_wallet', $seller_amount);

                $rfq->update([
                    'payment_status' => OrderStatus::PAID,
                    'status' => OrderStatus::COMPLETED,
                ]);

                $orderedItems = [
                    'product_name' => $product->name,
                    'image' => $product->front_image,
                    'quantity' => $rfq->product_quantity,
                    'price' => $seller_amount,
                    'buyer_name' => $user->first_name.' '.$user->last_name,
                    'order_number' => $orderNo,
                    'currency' => $user->default_currency,
                ];

                $orderItemData = [
                    'orderedItems' => $orderedItems,
                ];

                $type = MailingEnum::ORDER_EMAIL;
                $subject = 'B2B Order Confirmation';
                $mail_class = B2BOrderEmail::class;
                mailSend($type, $user, $subject, $mail_class, $orderItemData);

                (new UserLogAction(
                    request(),
                    UserLog::PAYMENT,
                    'Payment successful',
                    json_encode($paymentData),
                    $user
                ))->run();
            });
        } catch (\Exception $e) {
            Log::error('Error in handlePaymentSuccess: '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
            throw $e;
        }
    }

    public static function handleTransferSuccess(array $event): void
    {
        $transferCode = $event['transfer_code'];
        $reference = $event['reference'];

        $withdrawal = WithdrawalRequest::with('user')
            ->where('transfer_code', $transferCode)
            ->first();

        if (! $withdrawal) {
            Log::error("Transfer success: No matching withdrawal found for transfer_code: {$transferCode}");

            return;
        }

        DB::beginTransaction();
        try {
            $withdrawal->update([
                'status' => WithdrawalStatus::COMPLETED,
                'response' => $event['reason'],
            ]);

            $user = $withdrawal->user;
            $user->notify(new WithdrawalNotification($withdrawal, 'completed'));

            Log::info("Transfer successful for withdrawal ID {$withdrawal->id} - Reference: {$reference}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing transfer success: '.$e->getMessage());
            throw $e;
        }
    }

    public static function handleTransferFailed(array $event): void
    {
        $transferCode = $event['transfer_code'];
        $reference = $event['reference'];

        $withdrawal = WithdrawalRequest::with('user')
            ->where('transfer_code', $transferCode)
            ->first();

        if (! $withdrawal) {
            Log::error("Transfer success: No matching withdrawal found for transfer_code: {$transferCode}");

            return;
        }

        DB::beginTransaction();
        try {
            $withdrawal->update([
                'status' => WithdrawalStatus::FAILED,
                'response' => $event['reason'],
            ]);

            $user = $withdrawal->user->load(['wallet', 'userWallet']);

            if ($user->type === UserType::B2B_SELLER) {
                $user->userWallet->increment('master_wallet', $withdrawal->amount);
            } else {
                $user->wallet->increment('balance', $withdrawal->amount);
            }

            $user->notify(new WithdrawalNotification($withdrawal, 'failed'));

            Log::info("Transfer failed for withdrawal ID {$withdrawal->id} - Reference: {$reference}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing transfer success: '.$e->getMessage());
            throw $e;
        }
    }

    public static function handleTransferReversed(array $event): void
    {
        $transferCode = $event['transfer_code'];
        $reference = $event['reference'];

        $withdrawal = WithdrawalRequest::with('user')
            ->where('transfer_code', $transferCode)
            ->first();

        if (! $withdrawal) {
            Log::error("Transfer success: No matching withdrawal found for transfer_code: {$transferCode}");

            return;
        }

        DB::beginTransaction();
        try {
            $withdrawal->update([
                'status' => WithdrawalStatus::REVERSED,
                'response' => $event['reason'],
            ]);

            $user = $withdrawal->user->load(['wallet', 'userWallet']);

            if ($user->type === UserType::B2B_SELLER) {
                $user->userWallet->increment('master_wallet', $withdrawal->amount);
            } else {
                $user->wallet->increment('balance', $withdrawal->amount);
            }

            $user->notify(new WithdrawalNotification($withdrawal, 'failed'));

            Log::info("Transfer failed for withdrawal ID {$withdrawal->id} - Reference: {$reference}");

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing transfer success: '.$e->getMessage());
            throw $e;
        }
    }

    public static function createRecipient($fields, $method): void
    {
        $url = 'https://api.paystack.co/transferrecipient';
        $token = config('paystack.secretKey');
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ];
        $data = (new PostCurl($url, $headers, $fields))->execute();
        self::logTransfer($data, $method);
    }

    private static function orderNo(): string
    {
        do {
            $uniqueOrderNumber = 'ORD-'.now()->timestamp.'-'.Str::random(8);
        } while (Order::where('order_no', $uniqueOrderNumber)->exists());

        return $uniqueOrderNumber;
    }

    private static function sendSellerOrderEmail($seller, $order, string $orderNo, string $totalAmount): void
    {
        defer(fn () => send_email($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount)));
    }

    private static function sendOrderConfirmationEmail($user, $orderedItems, string $orderNo, string $totalAmount): void
    {
        defer(fn () => send_email($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount)));
    }

    private static function logTransfer(array $data, $method): void
    {
        $method->update([
            'recipient_code' => $data['recipient_code'],
            'data' => $data,
        ]);
    }

    private static function attachProductToOrder($item, $user, $order): array
    {
        $variationId = $item['variation_id'] ?? null;

        if ($variationId && $variationId > 0) {
            $variation = ProductVariation::with('product.user.wallet', 'product.shopCountry')->findOrFail($variationId);
            $product = $variation->product;
            $unitPrice = currencyConvert(
                $user->default_currency,
                $variation->price,
                $product->shopCountry?->currency
            );

            $order->products()->attach($product->id, [
                'product_quantity' => $item['product_quantity'],
                'price' => $unitPrice,
                'sub_total' => $unitPrice * $item['product_quantity'],
                'status' => OrderStatus::PENDING,
                'variation_id' => $variation->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $variation->decrement('stock', $item['product_quantity']);
            $image = $variation->image;
        } else {
            $product = Product::with(['user.wallet', 'shopCountry'])->findOrFail($item['product_id']);
            $unitPrice = currencyConvert(
                $user->default_currency,
                $product->price,
                $product->shopCountry?->currency
            );

            $order->products()->attach($product->id, [
                'product_quantity' => $item['product_quantity'],
                'price' => $unitPrice,
                'sub_total' => $unitPrice * $item['product_quantity'],
                'status' => OrderStatus::PENDING,
                'variation_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $product->decrement('current_stock_quantity', $item['product_quantity']);
            $image = $product->image;
        }

        if ($product->user) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $product->user->id],
                ['balance' => 0]
            );

            $amount = currencyConvert(
                $user->default_currency,
                $item['total_amount'],
                $product->shopCountry?->currency
            );

            $wallet->increment('balance', $amount);
        }

        return [
            'product_name' => $product->name,
            'image' => $image,
            'quantity' => $item['product_quantity'],
            'price' => $item['total_amount'],
            'currency' => $user->default_currency,
        ];
    }
}
