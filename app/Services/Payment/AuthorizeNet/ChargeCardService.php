<?php

namespace App\Services\Payment\AuthorizeNet;

use App\Models\Rfq;
use App\Models\Cart;
use App\Models\User;
use App\Enum\UserLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\B2bOrder;
use App\Enum\OrderStatus;
use App\Enum\PaymentType;
use App\Models\B2BProduct;
use App\Models\UserWallet;
use App\Mail\B2BOrderEmail;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Mail\SellerOrderMail;
use App\Models\Configuration;
use App\Actions\UserLogAction;
use App\Mail\CustomerOrderMail;
use App\Actions\PaymentLogAction;
use App\Contracts\PaymentStrategy;
use Illuminate\Support\Facades\Auth;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class ChargeCardService implements PaymentStrategy
{
    use HttpResponse;

    protected string $orderNo;

    public function __construct()
    {
        $this->orderNo = 'ORD-' . now()->timestamp . '-' . Str::random(8);

        while (Order::where('order_no', $this->orderNo)->exists()) {
            $this->orderNo = 'ORD-' . now()->timestamp . '-' . Str::random(8);
        }
    }

    public function processPayment(array $paymentDetails)
    {
        $user = Auth::user();
        $orderNo = $this->orderNo;
        $orderNum = Str::random(8);

        $merchantAuthentication = $this->getMerchantAuthentication();
        $payment = $this->getPayment($paymentDetails);
        $order = $this->getOrder($orderNum);
        $customerAddress = $this->getCustomerAddress($paymentDetails);
        $customerData = $this->getCustomerData($paymentDetails, $user);

        $transactionRequestType = $this->getTransactionRequestType($paymentDetails, $order, $payment, $customerAddress, $customerData);

        $this->addLineItems($transactionRequestType, $paymentDetails);

        $request = $this->createTransactionRequest($merchantAuthentication, $transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);

        $response = $this->executeTransaction($controller);

        return $this->handleResponse($response, $user, $paymentDetails, $orderNo, $payment);
    }
    //B2B Payment section
    public function ProcessB2BPayment(array $paymentDetails)
    {
        $user = Auth::user();
        $orderNo = $this->orderNo;
        $orderNum = Str::random(8);

        $merchantAuthentication = $this->getMerchantAuthentication();
        $payment = $this->getPayment($paymentDetails);
        $order = $this->getOrder($orderNum);
        $customerAddress = $this->getCustomerAddress($paymentDetails);
        $customerData = $this->getCustomerData($paymentDetails, $user);

        $transactionRequestType = $this->getTransactionRequestType($paymentDetails, $order, $payment, $customerAddress, $customerData);

        $request = $this->createTransactionRequest($merchantAuthentication, $transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);

        $response = $this->executeTransaction($controller);

        return $this->handleB2bResponse($response, $user, $paymentDetails, $orderNo, $payment);
    }

    private function handleB2bResponse($response, $user, array $paymentDetails, $orderNo, $payment)
    {
        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    return $this->handleB2bSuccessResponse($response, $tresponse, $user, $paymentDetails, $orderNo, $payment);
                }
                return $this->handleErrorResponse($tresponse, $response, $user);
            }
            return $this->handleErrorResponse(null, $response, $user);
        }
        return "No response returned \n";
    }

    private function handleB2bSuccessResponse($response, $tresponse, $user, array $paymentDetails, $orderNo, $payment)
    {
        $rfqId = $paymentDetails['rfq_id'];
        $amount = $paymentDetails['amount'];
        $data = (object)[
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'amount' => $amount,
            'reference' => generateRefCode(),
            'channel' => "card",
            'currency' => "USD",
            'ip_address' => request()->ip(),
            'paid_at' => now(),
            'createdAt' => now(),
            'transaction_date' => now(),
            'status' => "success",
            'type' => PaymentType::B2BUSERORDER,
        ];
       (new PaymentLogAction($data, $payment, 'authorizenet', 'success'))->execute();
        $rfq = Rfq::findOrFail($rfqId);
        $seller = User::findOrFail($rfq->seller_id);
        $product = B2BProduct::findOrFail($rfq->product_id);

        B2bOrder::create([
            'buyer_id' => $user->id,
            'seller_id' => $rfq->seller_id,
            'product_id' => $rfq->product_id,
            'product_quantity' => $rfq->product_quantity,
            'order_no' => $orderNo,
            'product_data' => $product,
            'total_amount' => $amount,
            'payment_method' => 'authorize-net',
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
        send_email($user->email, new B2BOrderEmail($orderedItems));
        (new UserLogAction(
            request(),
            UserLog::PAYMENT,
            "Payment successful",
            json_encode($response),
            $user
        ))->run();

        return $this->success(null, $tresponse->getMessages()[0]->getDescription());
    }
    private function getMerchantAuthentication(): \net\authorize\api\contract\v1\MerchantAuthenticationType
    {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorizenet.api_login_id'));
        $merchantAuthentication->setTransactionKey(config('services.authorizenet.transaction_key'));
        return $merchantAuthentication;
    }

    private function getPayment(array $paymentDetails): \net\authorize\api\contract\v1\PaymentType
    {
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($paymentDetails['payment']['creditCard']['cardNumber']);
        $creditCard->setExpirationDate($paymentDetails['payment']['creditCard']['expirationDate']);
        $creditCard->setCardCode($paymentDetails['payment']['creditCard']['cardCode']);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);
        return $payment;
    }

    private function getOrder($orderNo): \net\authorize\api\contract\v1\OrderType
    {
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($orderNo);
        $order->setDescription("Purchase of various items");
        return $order;
    }

    private function getCustomerAddress(array $paymentDetails): \net\authorize\api\contract\v1\CustomerAddressType
    {
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($paymentDetails['billTo']['firstName']);
        $customerAddress->setLastName($paymentDetails['billTo']['lastName']);
        $customerAddress->setCompany($paymentDetails['billTo']['company']);
        $customerAddress->setAddress($paymentDetails['billTo']['address']);
        $customerAddress->setCity($paymentDetails['billTo']['city']);
        $customerAddress->setState($paymentDetails['billTo']['state']);
        $customerAddress->setZip($paymentDetails['billTo']['zip']);
        $customerAddress->setCountry($paymentDetails['billTo']['country']);
        return $customerAddress;
    }

    private function getCustomerData(array $paymentDetails, $user): \net\authorize\api\contract\v1\CustomerDataType
    {
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($user->id);
        $customerData->setEmail($paymentDetails['customer']['email']);
        return $customerData;
    }

    private function getTransactionRequestType(array $paymentDetails, \net\authorize\api\contract\v1\OrderType $order, \net\authorize\api\contract\v1\PaymentType $payment, \net\authorize\api\contract\v1\CustomerAddressType $customerAddress, \net\authorize\api\contract\v1\CustomerDataType $customerData): \net\authorize\api\contract\v1\TransactionRequestType
    {
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($paymentDetails['amount']);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($payment);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);
        return $transactionRequestType;
    }

    private function addLineItems($transactionRequestType, array $paymentDetails): void
    {
        if (isset($paymentDetails['lineItems']) && is_array($paymentDetails['lineItems'])) {
            foreach ($paymentDetails['lineItems'] as $item) {
                $lineItem = new AnetAPI\LineItemType();
                $lineItem->setItemId($item['itemId']);
                $lineItem->setName($item['name']);
                $lineItem->setDescription($item['description']);
                $lineItem->setQuantity($item['quantity']);
                $lineItem->setUnitPrice($item['unitPrice']);

                $transactionRequestType->addToLineItems($lineItem);
            }
        }
    }

    private function createTransactionRequest(\net\authorize\api\contract\v1\MerchantAuthenticationType $merchantAuthentication, \net\authorize\api\contract\v1\TransactionRequestType $transactionRequestType): \net\authorize\api\contract\v1\CreateTransactionRequest
    {
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId("ref" . time());
        $request->setTransactionRequest($transactionRequestType);
        return $request;
    }

    private function executeTransaction(\net\authorize\api\controller\CreateTransactionController $controller)
    {
        if (app()->environment('production')) {
            return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    }

    private function handleResponse($response, $user, array $paymentDetails, $orderNo, $payment)
    {
        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    return $this->handleSuccessResponse($response, $tresponse, $user, $paymentDetails, $orderNo, $payment);
                }
                return $this->handleErrorResponse($tresponse, $response, $user);
            }
            return $this->handleErrorResponse(null, $response, $user);
        }
        return "No response returned \n";
    }

    private function handleSuccessResponse($response, $tresponse, $user, array $paymentDetails, $orderNo, $payment)
    {
        $amount = $paymentDetails['amount'];
        $data = (object)[
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'amount' => $amount,
            'reference' => generateRefCode(),
            'channel' => "card",
            'currency' => "USD",
            'ip_address' => request()->ip(),
            'paid_at' => now(),
            'createdAt' => now(),
            'transaction_date' => now(),
            'status' => "success",
            'type' => PaymentType::USERORDER,
        ];
        $pay = (new PaymentLogAction($data, $payment, 'authorizenet', 'success'))->execute();

        $orderedItems = [];
        $product = null;

        foreach ($paymentDetails['lineItems'] as $item) {
            try {
                $product = Product::with('user')->findOrFail($item['itemId']);

                Order::saveOrder(
                    $user,
                    $pay,
                    $product->user,
                    $item,
                    $orderNo,
                    $paymentDetails['billTo']['address'],
                    "authorizenet",
                    "success",
                );

                $orderedItems[] = [
                    'product_name' => $product->name,
                    'image' => $product->image,
                    'quantity' => $item['quantity'],
                    'price' => $item['unitPrice'],
                ];

                $product->decrement('current_stock_quantity', $item['quantity']);
            } catch (\Exception $e) {
                (new UserLogAction(
                    request(),
                    UserLog::PAYMENT,
                    "Order Processing Error for Item ID {$item['itemId']}: " . $e->getMessage(),
                    json_encode($paymentDetails),
                    $user
                ))->run();

                continue;
            }
        }

        Cart::where('user_id', $user->id)->delete();

        if ($product) {
            $this->sendSellerOrderEmail($product->user, $orderedItems, $orderNo, $amount);
        }
        $this->sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $amount);

        (new UserLogAction(
            request(),
            UserLog::PAYMENT,
            "Payment successful",
            json_encode($response),
            $user
        ))->run();

        return $this->success(null, $tresponse->getMessages()[0]->getDescription());
    }

    private function handleErrorResponse($tresponse, $response, $user)
    {
        $msg = $tresponse != null ? "Payment failed: " . $tresponse->getErrors()[0]->getErrorText() : "Payment failed: " . $response->getMessages()->getMessage()[0]->getText();

        (new UserLogAction(
            request(),
            UserLog::PAYMENT,
            $msg,
            json_encode($response),
            $user
        ))->run();

        return $this->error(null, $msg, 403);
    }

    private function sendSellerOrderEmail($seller, $order, $orderNo, $totalAmount): void
    {
        defer(fn() => send_email($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount)));
    }

    private function sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $totalAmount): void
    {
        defer(fn() => send_email($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount)));
    }
}
