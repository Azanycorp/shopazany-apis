<?php

namespace App\Services\Payment\AuthorizeNet;

use App\Trait\HttpResponse;
use Illuminate\Http\JsonResponse;
use App\Contracts\PaymentStrategy;
use Illuminate\Support\Facades\App;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class ChargeCardService implements PaymentStrategy
{
    use HttpResponse;

    public function processPayment(array $paymentDetails): JsonResponse
    {
        $cardNumber = $paymentDetails['card_number'];
        $expirationDate = $paymentDetails['expiration_date'];
        $cvv = $paymentDetails['cvv'];
        $amount = $paymentDetails['amount'];

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorizenet.api_login_id'));
        $merchantAuthentication->setTransactionKey(config('services.authorizenet.transaction_key'));

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($cardNumber);
        $creditCard->setExpirationDate($expirationDate);
        $creditCard->setCardCode($cvv);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($amount);
        $transactionRequestType->setPayment($payment);

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
}













