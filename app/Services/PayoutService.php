<?php

namespace App\Services;

use App\Enum\TransactionStatus;
use App\Models\User;
use App\Services\Curl\CurlService;
use App\Services\Curl\PostCurl;
use Illuminate\Support\Facades\Log;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class PayoutService
{
    public static function paystackTransfer($user, $fields)
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
                'message' => null,
                'data' => null
            ];
        }

        $amount = $fields['amount'];
        $formattedAmount = number_format($amount / 100, 2, '.', '');

        (new TransactionService(
            $user,
            TransactionStatus::TRANSFER,
            $formattedAmount,
            $data['status']
        ))->logTransaction();

        return [
            'status' => true,
            'message' => null,
            'data' => $data
        ];
    }

    public static function paystackBulkTransfer(array $transfers)
    {
        $url = "https://api.paystack.co/transfer/bulk";
        $token = config('paystack.secretKey');

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache',
        ];

        $body = [
            'currency' => 'NGN',
            'source' => 'balance',
            'transfers' => array_map(function ($t) {
                return [
                    'reference' => $t['reference'],
                    'amount' => $t['amount'],
                    'recipient' => $t['recipient'],
                    'reason' => $t['reason'],
                ];
            }, $transfers),
        ];

        $response = (new CurlService($url, $headers, $body))->execute();

        if (!isset($response['status']) || $response['status'] === false) {
            return [
                'status' => false,
                'message' => $response['message'],
                'data' => null
            ];
        }

        foreach ($transfers as $transfer) {
            $user = User::find($transfer['user_id']);
            if ($user) {
                $amount = $transfer['amount'];
                $formattedAmount = number_format($amount / 100, 2, '.', '');

                (new TransactionService(
                    $user,
                    TransactionStatus::TRANSFER,
                    $formattedAmount,
                    $response['status']
                ))->logTransaction();
            }
        }

        return [
            'status' => true,
            'message' => $response['message'] ?? 'Bulk transfer queued',
            'data' => $response['data']
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

