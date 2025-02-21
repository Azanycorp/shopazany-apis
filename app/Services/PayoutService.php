<?php

namespace App\Services;

use App\Enum\TransactionStatus;
use App\Services\Curl\PostCurl;
use Illuminate\Support\Facades\Log;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class PayoutService
{
    public static function paystackTransfer($request, $user, $fields)
    {
        $url = "https://api.paystack.co/transfer";
        $token = config('paystack.secretKey');

        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $data = (new PostCurl($url, $headers, $fields))->execute();

        if($data['status'] === false) {
            return [
                'status' => false,
                'message' => $data['message'],
                'data' => null
            ];
        }

        (new TransactionService(
            $user,
            TransactionStatus::TRANSFER,
            $fields['amount'],
            $data['status']
        ))->logTransaction();

        return [
            'status' => true,
            'message' => $data['message'],
            'data' => $data
        ];
    }

    public static function authorizeTransfer($request, $user, $fields)
    {
        try {
            $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
            $merchantAuthentication->setName(config('services.authorizenet.api_login_id'));
            $merchantAuthentication->setTransactionKey(config('services.authorizenet.transaction_key'));

            $refId = 'ref' . time();
            $randomAccountNumber = $fields['account_number'];

            // Ensure routing number is correctly formatted
            $routingNumber = substr($fields['routing_number'], 0, 9);

            // Create the payment data for a Bank Account
            $bankAccount = new AnetAPI\BankAccountType();
            $bankAccount->setAccountType('checking');
            $bankAccount->setRoutingNumber($routingNumber);
            $bankAccount->setAccountNumber($randomAccountNumber);
            $bankAccount->setNameOnAccount($fields['account_name']);
            $bankAccount->setBankName($fields['bank_name']);

            $paymentBank = new AnetAPI\PaymentType();
            $paymentBank->setBankAccount($bankAccount);

            // Create transaction request
            $transactionRequestType = new AnetAPI\TransactionRequestType();
            $transactionRequestType->setTransactionType("refundTransaction");
            $transactionRequestType->setAmount($request->amount);
            $transactionRequestType->setPayment($paymentBank);

            $anetRequest = new AnetAPI\CreateTransactionRequest();
            $anetRequest->setMerchantAuthentication($merchantAuthentication);
            $anetRequest->setRefId($refId);
            $anetRequest->setTransactionRequest($transactionRequestType);

            $controller = new AnetController\CreateTransactionController($anetRequest);
            $response = self::executeTransaction($controller);

            // If no response, return failure
            if ($response == null) {
                Log::error("Authorize.net transaction failed: No response received");
                return [
                    'status' => false,
                    'message' => "Authorize.net transaction failed: No response received",
                    'data' => null
                ];
            }

            // Check response result code
            if ($response->getMessages()->getResultCode() === "Ok") {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    // Log successful transaction
                    (new TransactionService(
                        $user,
                        TransactionStatus::TRANSFER,
                        $fields['amount'],
                        "success"
                    ))->logTransaction();

                    return [
                        'status' => true,
                        'message' => $tresponse->getMessages()[0]->getDescription(),
                        'data' => $tresponse
                    ];
                }
            }

            // If transaction failed, get error message
            $tresponse = $response->getTransactionResponse();
            $errorMessage = ($tresponse != null && $tresponse->getErrors() != null) ?
                $tresponse->getErrors()[0]->getErrorText() :
                $response->getMessages()->getMessage()[0]->getText();

            Log::error("Authorize.net transaction failed: " . $errorMessage);

            return [
                'status' => false,
                'message' => $errorMessage,
                'data' => null
            ];

        } catch (\Exception $e) {
            Log::error("Authorize.net Exception: " . $e->getMessage());
            return [
                'status' => false,
                'message' => "Authorize.net Exception: " . $e->getMessage(),
                'data' => null
            ];
        }
    }

    private static function executeTransaction(\net\authorize\api\controller\CreateTransactionController $controller)
    {
        if (app()->environment('production')) {
            return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    }
}

