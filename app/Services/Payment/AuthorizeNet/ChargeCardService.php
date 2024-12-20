<?php

namespace App\Services\Payment\AuthorizeNet;

use App\Actions\PaymentLogAction;
use App\Trait\HttpResponse;
use Illuminate\Http\JsonResponse;
use App\Contracts\PaymentStrategy;
use App\Enum\PaymentType;
use App\Mail\CustomerOrderMail;
use App\Mail\SellerOrderMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class ChargeCardService implements PaymentStrategy
{
    use HttpResponse;

    public function processPayment(array $paymentDetails): JsonResponse
    {
        $user = auth()->user();

        $cardNumber = $paymentDetails['payment']['creditCard']['cardNumber'];
        $expirationDate = $paymentDetails['payment']['creditCard']['expirationDate'];
        $cvv = $paymentDetails['payment']['creditCard']['cardCode'];
        $amount = $paymentDetails['amount'];
        $orderNo = rand(100000, 999999);

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorizenet.api_login_id'));
        $merchantAuthentication->setTransactionKey(config('services.authorizenet.transaction_key'));

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cardNumber);
        $creditCard->setExpirationDate($expirationDate);
        $creditCard->setCardCode($cvv);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);

        // Create order information
        $order = new AnetAPI\OrderType();
        $order->setInvoiceNumber($orderNo);
        $order->setDescription("Purchase of various items");

        // Set the customer's Bill To address
        $customerAddress = new AnetAPI\CustomerAddressType();
        $customerAddress->setFirstName($paymentDetails['billTo']['firstName']);
        $customerAddress->setLastName($paymentDetails['billTo']['lastName']);
        $customerAddress->setCompany($paymentDetails['billTo']['company']);
        $customerAddress->setAddress($paymentDetails['billTo']['address']);
        $customerAddress->setCity($paymentDetails['billTo']['city']);
        $customerAddress->setState($paymentDetails['billTo']['state']);
        $customerAddress->setZip($paymentDetails['billTo']['zip']);
        $customerAddress->setCountry($paymentDetails['billTo']['country']);

        // Set the customer's identifying information
        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($user->id);
        $customerData->setEmail($paymentDetails['customer']['email']);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($payment);
        $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);

        // Add multiple line items
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

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId("ref" . time());
        $request->setTransactionRequest($transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);

        $response = $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);

        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    $data = (object)[
                        'user_id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'amount' => $amount,
                        'reference' => rand(100000, 999999),
                        'channel' => "card",
                        'currency' => "USD",
                        'ip_address' => "192.168.1.1",
                        'paid_at' => now(),
                        'createdAt' => now(),
                        'transaction_date' => now(),
                        'status' => "success",
                        'type' => PaymentType::USERORDER,
                    ];

                    (new PaymentLogAction($data, $payment, 'authorizenet', 'success'))->execute();

                    $orderedItems = [];
                    foreach ($paymentDetails['lineItems'] as $item) {

                        $product = Product::with('user')
                            ->findOrFail($item['itemId']);

                        Order::saveOrder(
                            $user,
                            $payment,
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
                    }

                    Cart::where('user_id', $user->id)->delete();

                    self::sendOrderConfirmationEmail($user, $orderedItems, $orderNo, $amount);
                    self::sendSellerOrderEmail($product->user, $orderedItems, $orderNo, $amount);
                    return $this->success(null, $tresponse->getMessages()[0]->getDescription());
                } else {
                    $msg = "Payment failed: " . $tresponse->getErrors()[0]->getErrorText();
                    return $this->error(null, $msg, 403);
                }
            } else {
                $msg = "Payment failed: " . $response->getMessages()->getMessage()[0]->getText();
                return $this->error(null, $msg, 403);
            }
        } else {
            return "No response returned \n";
        }
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













