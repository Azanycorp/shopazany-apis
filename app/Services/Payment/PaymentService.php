<?php

namespace App\Services\Payment;

use App\Models\Cart;
use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PaymentLog;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;
use App\Models\UserShippingAddress;
use Illuminate\Support\Facades\Auth;
use App\Services\Curl\GetCurlService;
use App\Http\Resources\PaymentVerifyResource;
use App\Mail\CustomerOrderMail;
use App\Mail\SellerOrderMail;
use Unicodeveloper\Paystack\Facades\Paystack;

class PaymentService
{
    use HttpResponse;

    public function processPayment($request)
    {
        $user = User::findOrFail($request->user_id);

        $amount = $request->input('amount') * 100;
        $user_shipping = $request->input('user_shipping_address_id');

        if (!$user_shipping) {
            $address = (object) [
                'first_name' => $request->shipping_address['first_name'],
                'last_name' => $request->shipping_address['last_name'],
                'email' => $request->shipping_address['email'],
                'phone' => $request->shipping_address['phone'],
                'street_address' => $request->shipping_address['street_address'],
                'state' => $request->shipping_address['state'],
                'city' => $request->shipping_address['city'],
                'zip' => $request->shipping_address['zip'],
            ];
        } else {
            $addr = $user->userShippingAddress()->where('id', $user_shipping)->first();
            $address = $addr;
        }

        $callbackUrl = $request->input('payment_redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return response()->json(['error' => 'Invalid callback URL'], 400);
        }

        $paymentDetails = [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => 'NGN',
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'shipping_address' => $address,
                'items' => $request->input('items'),
                'payment_method' => $request->input('payment_method'),
            ]),
            'callback_url' => $request->input('payment_redirect_url')
        ];

        $paystackInstance = Paystack::getAuthorizationUrl($paymentDetails);
        return response()->json($paystackInstance);
    }

    public function webhook($request)
    {
        $secretKey = config('paystack.secretKey');
        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$signature || $signature !== hash_hmac('sha512', $payload, $secretKey)) {
            return $this->error(null, 'Invalid signature', 400);
        }

        $event = json_decode($payload, true);

        if (isset($event['event']) && $event['event'] === 'charge.success') {
            $this->handlePaymentSuccess($event, $event['event']);
        }

        return response()->json(['status' => true], 200);
    }

    public function verifyPayment($userId, $ref)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $verify = (new GetCurlService($ref))->run();
        $data = new PaymentVerifyResource($verify);

        return $this->success($data, "Payment verify status");
    }

    protected function handlePaymentSuccess($event, $status)
    {
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
            $orderNo = $this->orderNo();

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
            ]);

            PaymentLog::create([
                'payment_id' => $payment->id,
                'data' => $paymentData,
                'method' => $method,
                'status' => $status,
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

            $this->sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $formattedAmount);
            $this->sendSellerOrderEmail($seller->user, $orderedItems, $orderNo, $formattedAmount);
        });
    }

    private function orderNo()
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

    private function sendSellerOrderEmail($seller, $order, $orderNo, $totalAmount)
    {
        defer(fn() => send_email($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount)));
    }

    private function sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $totalAmount)
    {
        defer(fn() => send_email($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount)));
    }
}



