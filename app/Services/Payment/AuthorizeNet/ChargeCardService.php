<?php

namespace App\Services\Payment\AuthorizeNet;

use App\Actions\PaymentLogAction;
use App\Actions\UserLogAction;
use App\Contracts\PaymentStrategy;
use App\Enum\MailingEnum;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Enum\UserLog;
use App\Http\Resources\B2BBuyerShippingAddressResource;
use App\Mail\B2BOrderEmail;
use App\Mail\CustomerOrderMail;
use App\Mail\SellerOrderMail;
use App\Models\B2bOrder;
use App\Models\B2BProduct;
use App\Models\BuyerShippingAddress;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\Rfq;
use App\Models\ShippingAgent;
use App\Models\User;
use App\Models\UserShippingAddress;
use App\Models\UserWallet;
use App\Models\Wallet;
use App\Services\Auth\Auth as ServicesAuth;
use App\Services\Auth\RequestOptions;
use App\Trait\HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ChargeCardService implements PaymentStrategy
{
    use HttpResponse;

    protected string $orderNo;

    public function __construct(protected ServicesAuth $servicesAuth)
    {
        $this->orderNo = 'ORD-'.now()->timestamp.'-'.Str::random(8);

        while (Order::where('order_no', $this->orderNo)->exists()) {
            $this->orderNo = 'ORD-'.now()->timestamp.'-'.Str::random(8);
        }
    }

    public function processPayment(array $paymentDetails): JsonResponse|array
    {
        $user = Auth::user();
        $orderNo = $this->orderNo;
        $requestClass = resolve(Request::class);
        $items = $paymentDetails['lineItems'];
        $productIds = (new Collection($items))->pluck('itemId')->toArray();

        if ($validateQuantity = $this->validateProductQuantity($productIds, $items)) {
            return $validateQuantity;
        }

        $url = config('services.payment_service.url').'/authorize/initialize';
        $result = $this->servicesAuth->post($url, new RequestOptions(
            data: [
                'data' => $this->buildChargePayload($paymentDetails, $user, $orderNo, $items),
            ]
        ));

        return $this->handleResponse($result, $user, $paymentDetails, $orderNo, $requestClass);
    }

    /**
     * @param  array<string, mixed>  $paymentDetails
     * @param  array<string, mixed>  $items
     * @return array<string, mixed>
     */
    private function buildChargePayload(array $paymentDetails, User $user, string $orderNo, array $items): array
    {
        return [
            'amount' => $paymentDetails['amount'],
            'reference' => $orderNo,
            'description' => 'Purchase of various items',
            'card' => [
                'number' => $paymentDetails['payment']['creditCard']['cardNumber'],
                'expiration' => $paymentDetails['payment']['creditCard']['expirationDate'],
                'cvv' => $paymentDetails['payment']['creditCard']['cardCode'],
            ],
            'bill_to' => [
                'first_name' => $paymentDetails['billTo']['firstName'],
                'last_name' => $paymentDetails['billTo']['lastName'],
                'company' => $paymentDetails['billTo']['company'] ?? null,
                'address' => $paymentDetails['billTo']['address'],
                'city' => $paymentDetails['billTo']['city'],
                'state' => $paymentDetails['billTo']['state'],
                'zip' => $paymentDetails['billTo']['zip'],
                'country' => $paymentDetails['billTo']['country'],
            ],
            'customer' => [
                'id' => $user->id,
                'email' => $paymentDetails['customer']['email'],
            ],
            'line_items' => array_map(fn ($item) => [
                'item_id' => $item['itemId'],
                'name' => $item['name'],
                'description' => $item['description'] ?? '',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unitPrice'],
            ], $items),
        ];
    }

    private function handleResponse(
        array $result,
        User $user,
        array $paymentDetails,
        string $orderNo,
        Request $request
    ): JsonResponse {
        if ($result['status'] ?? false) {
            return $this->handleSuccessResponse($result, $user, $paymentDetails, $orderNo, $request);
        }

        return $this->handleErrorResponse($result, $user, $request);
    }

    private function handleSuccessResponse(array $result, User $user, array $paymentDetails, string $orderNo, Request $request)
    {
        $amount = $paymentDetails['amount'];
        $data = (object) [
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'amount' => $amount,
            'reference' => generateRefCode(),
            'channel' => 'card',
            'currency' => 'USD',
            'ip_address' => $request->ip(),
            'paid_at' => now(),
            'createdAt' => now(),
            'transaction_date' => now(),
            'status' => 'success',
            'type' => PaymentType::USERORDER,
        ];
        $pay = (new PaymentLogAction($data, $result['data'], 'authorizenet', 'success'))->execute();

        $totalAmount = 0;

        foreach ($paymentDetails['lineItems'] as $item) {
            $product = Product::with('user', 'shopCountry')->findOrFail($item['itemId']);

            $convertedPrice = currencyConvert(
                $user->default_currency,
                $item['unitPrice'],
                $product->shopCountry?->currency
            );
            $totalAmount += $convertedPrice * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => $user->id,
            'payment_id' => $pay->id,
            'order_no' => $orderNo,
            'total_amount' => $totalAmount,
            'payment_method' => 'authorizenet',
            'payment_status' => 'success',
            'order_date' => now(),
            'shipping_address' => $paymentDetails['billTo']['address'],
            'country_id' => $user->country ?? 160,
            'status' => OrderStatus::PENDING,
        ]);

        $order->markAsAgriecom($user->id);

        $orderedItems = [];
        $product = null;

        foreach ($paymentDetails['lineItems'] as $item) {
            try {
                $variationId = $item['variation_id'] ?? null;

                if ($variationId && $variationId > 0) {
                    $variation = ProductVariation::with('product.user.wallet', 'product.shopCountry')->findOrFail($variationId);
                    $product = $variation->product;

                    $convertedPrice = currencyConvert(
                        $user->default_currency,
                        $item['unitPrice'],
                        $product->shopCountry?->currency
                    );

                    $order->products()->attach($product->id, [
                        'product_quantity' => $item['quantity'],
                        'variation_id' => $variation->id,
                        'price' => $convertedPrice,
                        'sub_total' => $convertedPrice * $item['quantity'],
                        'status' => OrderStatus::PENDING,
                    ]);

                    $orderedItems[] = [
                        'product_name' => $product->name,
                        'image' => $product->image,
                        'quantity' => $item['quantity'],
                        'price' => $convertedPrice,
                        'currency' => $product->shopCountry?->currency,
                    ];

                    $variation->decrement('stock', $item['quantity']);
                } else {
                    $product = Product::with(['user', 'shopCountry'])->findOrFail($item['itemId']);

                    $convertedPrice = currencyConvert(
                        $user->default_currency,
                        $item['unitPrice'],
                        $product->shopCountry?->currency
                    );

                    $order->products()->attach($product->id, [
                        'product_quantity' => $item['quantity'],
                        'variation_id' => null,
                        'price' => $convertedPrice,
                        'sub_total' => $convertedPrice * $item['quantity'],
                        'status' => OrderStatus::PENDING,
                    ]);

                    $orderedItems[] = [
                        'product_name' => $product->name,
                        'image' => $product->image,
                        'quantity' => $item['quantity'],
                        'price' => $convertedPrice,
                        'currency' => $product->shopCountry?->currency,
                    ];

                    $product->decrement('current_stock_quantity', $item['quantity']);
                }

                if ($product->user) {
                    $wallet = Wallet::firstOrCreate(
                        ['user_id' => $product->user->id],
                        ['balance' => 0]
                    );

                    $amount = currencyConvert(
                        $user->default_currency,
                        $item['unitPrice'],
                        $product->shopCountry->currency,
                    );

                    $wallet->increment('balance', $amount);
                }
            } catch (\Exception $e) {
                (new UserLogAction(
                    $request,
                    UserLog::PAYMENT,
                    "Order Processing Error for Item ID {$item['itemId']}: ".$e->getMessage(),
                    json_encode($paymentDetails),
                    $user
                ))->run();

                continue;
            }
        }

        UserShippingAddress::updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $paymentDetails['billTo']['firstName'],
                'last_name' => $paymentDetails['billTo']['lastName'],
                'email' => $paymentDetails['customer']['email'],
                'phone' => '0000000000',
                'street_address' => $paymentDetails['billTo']['address'],
                'state' => $paymentDetails['billTo']['state'],
                'city' => $paymentDetails['billTo']['city'],
                'zip' => $paymentDetails['billTo']['zip'],
            ]
        );

        Cart::where('user_id', $user->id)->delete();

        if ($product->user) {
            $this->sendSellerOrderEmail($product->user, $orderedItems, $orderNo, $amount);
        }

        $this->sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $amount);

        (new UserLogAction(
            $request,
            UserLog::PAYMENT,
            'Payment successful',
            json_encode($result),
            $user
        ))->run();

        return $this->success(['order_no' => $orderNo], $result['message']);
    }

    private function handleErrorResponse(array $result, User $user, Request $request): JsonResponse
    {
        $msg = 'Payment failed: '.($result['message'] ?? 'Unknown error');

        (new UserLogAction($request, UserLog::PAYMENT, $msg, json_encode($result), $user))->run();

        return $this->error(null, $msg, 403);
    }

    /**
     * @param  array<int>  $productIds
     * @param  array<string, mixed>  $items
     * @return array<string, mixed>|null
     */
    private function validateProductQuantity(array $productIds, array $items): ?array
    {
        $products = Product::whereIn('id', $productIds)->get();

        if ($products->count() !== count($productIds)) {
            return [
                'status' => false,
                'message' => 'One or more products not found',
                'data' => null,
            ];
        }

        foreach ($items as $item) {
            $product = $products->firstWhere('id', $item['itemId']);

            if ($item['quantity'] > $product->current_stock_quantity) {
                return [
                    'status' => false,
                    'message' => "Only {$product->current_stock_quantity} unit(s) of {$product->name} are available",
                    'data' => null,
                ];
            }
        }

        return null;
    }

    private function sendSellerOrderEmail(User $seller, array $order, string $orderNo, float $totalAmount): void
    {
        sendEmail($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount));
    }

    private function sendOrderConfirmationEmail(User $user, array $orderedItems, string $orderNo, float $totalAmount): void
    {
        sendEmail($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount));
    }

    // B2B Payment section
    public function ProcessB2BPayment(array $paymentDetails)
    {
        $user = Auth::user();
        $orderNo = $this->orderNo;
        $requestClass = resolve(Request::class);

        $url = config('services.payment_service.url').'/authorize/initialize';
        $result = $this->servicesAuth->post($url, new RequestOptions(
            data: [
                'data' => [
                    'amount' => $paymentDetails['amount'],
                    'reference' => $orderNo,
                    'description' => 'B2B order payment',
                    'card' => [
                        'number' => $paymentDetails['payment']['creditCard']['cardNumber'],
                        'expiration' => $paymentDetails['payment']['creditCard']['expirationDate'],
                        'cvv' => $paymentDetails['payment']['creditCard']['cardCode'],
                    ],
                    'bill_to' => [
                        'first_name' => $paymentDetails['billTo']['firstName'],
                        'last_name' => $paymentDetails['billTo']['lastName'],
                        'company' => $paymentDetails['billTo']['company'] ?? null,
                        'address' => $paymentDetails['billTo']['address'],
                        'city' => $paymentDetails['billTo']['city'],
                        'state' => $paymentDetails['billTo']['state'],
                        'zip' => $paymentDetails['billTo']['zip'],
                        'country' => $paymentDetails['billTo']['country'],
                    ],
                    'customer' => [
                        'id' => $user->id,
                        'email' => $paymentDetails['customer']['email'],
                    ],
                ],
            ]
        ));

        return $this->handleB2bResponse($result, $user, $paymentDetails, $orderNo, $requestClass);
    }

    private function handleB2bResponse(
        array $result,
        User $user,
        array $paymentDetails,
        string $orderNo,
        Request $request
    ): JsonResponse {
        if ($result['status'] ?? false) {
            return $this->handleB2bSuccessResponse($result, $user, $paymentDetails, $orderNo, $request);
        }

        return $this->handleErrorResponse($result, $user, $request);
    }

    private function handleB2bSuccessResponse(
        array $result,
        User $user,
        array $paymentDetails,
        string $orderNo,
        Request $request
    ): JsonResponse {
        $rfqId = $paymentDetails['rfq_id'];
        $centerId = $paymentDetails['centre_id'];
        $type = $paymentDetails['type'];
        $shipping_address_id = $paymentDetails['shipping_address_id'];
        $shipping_agent_id = $paymentDetails['shipping_agent_id'];
        $billing_address = $paymentDetails['billTo'];
        $amount = $paymentDetails['amount'];
        $data = (object) [
            'user_id' => $user->id,
            'centre_id' => $centerId ?? null,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'amount' => $amount,
            'reference' => generateRefCode(),
            'channel' => 'card',
            'currency' => 'USD',
            'ip_address' => $request->ip(),
            'paid_at' => now(),
            'createdAt' => now(),
            'transaction_date' => now(),
            'status' => 'success',
            'type' => PaymentType::B2BUSERORDER,
        ];

        (new PaymentLogAction($data, $result['data'], 'authorizenet', 'success'))->execute();

        if ($shipping_agent_id) {
            $shipping_agent = ShippingAgent::findOrFail($shipping_agent_id);
        }
        $rfq = Rfq::findOrFail($rfqId);
        $seller = User::findOrFail($rfq->seller_id);
        $buyer = User::findOrFail($rfq->buyer_id);
        $product = B2BProduct::findOrFail($rfq->product_id);
        $saddress = BuyerShippingAddress::findOrFail($shipping_address_id);
        $shipping_address = new B2BBuyerShippingAddressResource($saddress);

        $buyer_amount = currencyConvert(
            $product->shopCountry->currency ?? 'USD',
            $amount,
            $buyer->default_currency,
        );

        $order = B2bOrder::create([
            'buyer_id' => $user->id,
            'centre_id' => $centerId ?? null,
            'seller_id' => $rfq->seller_id,
            'product_id' => $rfq->product_id,
            'product_quantity' => $rfq->product_quantity,
            'order_no' => $orderNo,
            'product_data' => $rfq->product_data,
            'shipping_address' => $shipping_address,
            'shipping_agent' => $shipping_agent_id ? $shipping_agent->name : '',
            'billing_address' => $billing_address,
            'seller_unit_price' => $rfq->seller_unit_price,
            'buyer_unit_price' => $rfq->buyer_unit_price,
            'buyer_total_amount' => $buyer_amount,
            'seller_total_amount' => $amount,
            'total_amount' => $amount,
            'payment_method' => 'authorize-net',
            'payment_status' => OrderStatus::PAID,
            'status' => OrderStatus::PENDING,
            'type' => $type ?? null,
        ]);

        $order->orderStages()->create([
            'message' => 'Your order has been placed successfully.',
            'status' => 'Order Placed',
            'current_location' => 'Online',
            'date' => now(),
        ]);

        $orderedItems = [
            'product_name' => $product->name,
            'image' => $product->front_image,
            'quantity' => $rfq->product_quantity,
            'price' => $rfq->total_amount,
            'buyer_name' => $user->first_name.' '.$user->last_name,
            'order_number' => $orderNo,
        ];

        $orderItemData = ['orderedItems' => $orderedItems];

        $product->availability_quantity -= $rfq->product_quantity;
        $product->sold += $rfq->product_quantity;
        $product->save();

        $wallet = UserWallet::firstOrCreate(
            ['seller_id' => $seller->id],
            ['master_wallet' => 0]
        );
        $wallet->increment('master_wallet', $amount);
        $rfq->update([
            'payment_status' => OrderStatus::PAID,
            'status' => OrderStatus::COMPLETED,
        ]);

        $type = MailingEnum::ORDER_EMAIL;
        $subject = 'B2B Order Confirmation';
        $mail_class = B2BOrderEmail::class;
        mailSend($type, $user, $subject, $mail_class, $orderItemData);

        (new UserLogAction(
            $request,
            UserLog::PAYMENT,
            'Payment successful',
            json_encode($result),
            $user
        ))->run();

        return $this->success(null, $result['message']);
    }
}
