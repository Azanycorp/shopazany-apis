<?php

namespace App\Services\Payment;

use App\Actions\PaymentLogAction;
use App\Actions\UserLogAction;
use App\Contracts\PaymentStrategy;
use App\Enum\PaymentType;
use App\Enum\SubscriptionType;
use App\Enum\UserLog;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Trait\HttpResponse;
use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\controller as AnetController;

class AuthorizeNetSubscriptionPaymentProcessor implements PaymentStrategy
{
    use HttpResponse;

    public function processPayment(array $paymentDetails): array
    {
        $user = userAuth();

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication->setName(config('services.authorizenet.api_login_id'));
        $merchantAuthentication->setTransactionKey(config('services.authorizenet.transaction_key'));

        $creditCard = new AnetAPI\CreditCardType();
        $creditCard->setCardNumber($paymentDetails['card_number']);
        $creditCard->setExpirationDate($paymentDetails['expiration_date']);
        $creditCard->setCardCode($paymentDetails['card_code']);

        $payment = new AnetAPI\PaymentType();
        $payment->setCreditCard($creditCard);

        $customerData = new AnetAPI\CustomerDataType();
        $customerData->setType("individual");
        $customerData->setId($user->id);
        $customerData->setEmail($paymentDetails['email']);

        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $transactionRequestType->setTransactionType("authCaptureTransaction");
        $transactionRequestType->setAmount($paymentDetails['amount']);
        // $transactionRequestType->setOrder($order);
        $transactionRequestType->setPayment($payment);
        // $transactionRequestType->setBillTo($customerAddress);
        $transactionRequestType->setCustomer($customerData);

        $request = new AnetAPI\CreateTransactionRequest();
        $request->setMerchantAuthentication($merchantAuthentication);
        $request->setRefId("ref" . time());
        $request->setTransactionRequest($transactionRequestType);

        $controller = new AnetController\CreateTransactionController($request);

        $response = $this->executeTransaction($controller);

        if ($response != null) {
            if ($response->getMessages()->getResultCode() == "Ok") {
                $tresponse = $response->getTransactionResponse();

                if ($tresponse != null && $tresponse->getMessages() != null) {
                    return $this->handleSuccessResponse($response, $tresponse, $user, $paymentDetails, $payment);
                }
                return $this->handleErrorResponse($tresponse, $response, $user);
            }
            return $this->handleErrorResponse(null, $response, $user);
        }
        return ['error' => 'No response from Authorize.net'];
    }

    private function executeTransaction(\net\authorize\api\controller\CreateTransactionController $controller)
    {
        if (app()->environment('production')) {
            return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::PRODUCTION);
        }
        return $controller->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::SANDBOX);
    }


    private function handleSuccessResponse($response, $tresponse, $user, array $paymentDetails, $payment)
    {
        $subUser = User::findOrFail($user->id);
        $referrer = User::with(['wallet'])->find($paymentDetails['referrer_id']);

        $activeSubscription = $user->subscription_plan;
        if ($activeSubscription) {
            $activeSubscription->update([
                'status' => SubscriptionType::EXPIRED,
                'expired_at' => now(),
            ]);
        }

        $data = (object)[
            'user_id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'amount' => $paymentDetails['amount'],
            'reference' => generateRefCode(),
            'channel' => "card",
            'currency' => $paymentDetails['currency'],
            'ip_address' => request()->ip(),
            'paid_at' => now(),
            'createdAt' => now(),
            'transaction_date' => now(),
            'status' => "success",
            'type' => PaymentType::RECURRINGCHARGE,
        ];

        $method = PaymentType::AUTHORIZE;

        $payLog = (new PaymentLogAction($data, $payment, $method, "success"))->execute();

        $user->userSubscriptions()->create([
            'subscription_plan_id' => $paymentDetails['subscription_plan_id'],
            'payment_id' => $payLog->id,
            'plan_start' => now(),
            'plan_end' => now()->addDays(30),
            'subscription_type' => PaymentType::AUTHORIZE,
            'authorization_data' => null,
            'status' => SubscriptionType::ACTIVE,
            'expired_at' => null,
        ]);

        SubscriptionService::creditAffiliate($referrer,$paymentDetails['amount'], $subUser);

        (new UserLogAction(
            request(),
            UserLog::SUBSCRIPTION_PAYMENT,
            "Payment successful",
            json_encode($response),
            $user
        ))->run();

        return [
            'status' => 'success',
            'message' => $tresponse->getMessages()[0]->getDescription(),
            'data' => null
        ];
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

        return [
            'status' => 'error',
            'message' => $msg,
            'data' => null
        ];
    }
}


