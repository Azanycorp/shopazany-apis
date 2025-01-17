<?php

namespace App\Services\Payment\B2B\AuthorizeNet;

use App\Models\Cart;
use App\Enum\UserLog;
use App\Models\Order;
use App\Models\Product;
use App\Enum\PaymentType;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Mail\SellerOrderMail;
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

    protected $orderNo;

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

    private function getMerchantAuthentication()
    {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorizenet.api_login_id'));
        $merchantAuthentication->setTransactionKey(config('services.authorizenet.transaction_key'));
        return $merchantAuthentication;
    }

    private function getPayment(array $paymentDetails)
    {
        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($paymentDetails['payment']['creditCard']['cardNumber']);
        $creditCard->setExpirationDate($paymentDetails['payment']['creditCard']['expirationDate']);
        $creditCard->setCardCode($paymentDetails['payment']['creditCard']['cardCode']);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);
        return $payment;
    }

    private function getOrder($orderNo)
    {
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($orderNo);
        $order->setDescription("Purchase of various items");
        return $order;
    }

    private function getCustomerAddress(array $paymentDetails)
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

    private function getCustomerData(array $paymentDetails, $user)
    {
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($user->id);
        $customerData->setEmail($paymentDetails['customer']['email']);
        return $customerData;
    }

    private function getTransactionRequestType(array $paymentDetails, $order, $payment, $customerAddress, $customerData)
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

    private function addLineItems($transactionRequestType, array $paymentDetails)
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

    private function createTransactionRequest($merchantAuthentication, $transactionRequestType)
    {
        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId("ref" . time());
        $request->setTransactionRequest($transactionRequestType);
        return $request;
    }

    private function executeTransaction($controller)
    {
        if (app()->environment('production')) {
            return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        } else {
            return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
        }
    }

    private function handleResponse($response, $user, $paymentDetails, $orderNo, $payment)
    {
        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    return $this->handleSuccessResponse($response, $tresponse, $user, $paymentDetails, $orderNo, $payment);
                } else {
                    return $this->handleErrorResponse($tresponse, $response, $user);
                }
            } else {
                return $this->handleErrorResponse(null, $response, $user);
            }
        } else {
            return "No response returned \n";
        }
    }

    private function handleSuccessResponse($response, $tresponse, $user, $paymentDetails, $orderNo, $payment)
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
            self::sendSellerOrderEmail($product->user, $orderedItems, $orderNo, $amount);
        }
        self::sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $amount);

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

    private static function sendSellerOrderEmail($seller, $order, $orderNo, $totalAmount)
    {
        defer(fn() => send_email($seller->email, new SellerOrderMail($seller, $order, $orderNo, $totalAmount)));
    }

    private static function sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $totalAmount)
    {
        defer(fn() => send_email($user->email, new CustomerOrderMail($user, $orderedItems, $orderNo, $totalAmount)));
    }
}













