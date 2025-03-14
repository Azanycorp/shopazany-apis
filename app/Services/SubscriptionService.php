<?php

namespace App\Services;

use App\Enum\PaymentType;
use App\Http\Resources\SubscriptionHistoryResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Services\Payment\AuthorizeNetSubscriptionPaymentProcessor;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaystackPaymentProcessor;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;

class SubscriptionService
{
    use HttpResponse;

    public function getPlanByCountry($countryId)
    {
        $type = request()->query('type', 'b2c');

        $plan = SubscriptionPlan::where('country_id', $countryId)
            ->where('type', $type)
            ->get();
        $data = SubscriptionPlanResource::collection($plan);

        return $this->success($data, "Subscription plans");
    }

    public function subscriptionPayment($request)
    {
        $paymentProcessor = match ($request->type) {
            PaymentType::PAYSTACK => new PaystackPaymentProcessor(),
            PaymentType::AUTHORIZE => new AuthorizeNetSubscriptionPaymentProcessor(),
            default => throw new \Exception("Unsupported payment method"),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = match($request->type) {
            PaymentType::PAYSTACK => PaymentDetailsService::paystackSubcriptionPayDetails($request),
            PaymentType::AUTHORIZE => PaymentDetailsService::authorizeNetSubcriptionPayDetails($request),
            default => throw new \Exception("Unsupported payment method"),
        };

        if (isset($paymentDetails['error'])) {
            return $this->error(null, $paymentDetails['error'], 400);
        }

        return $paymentService->process($paymentDetails);
    }

    public function subscriptionHistory($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with(['userSubscriptions.subscriptionPlan'])
            ->findOrFail($userId)
            ->append('subscription_history');

        $data = SubscriptionHistoryResource::collection($user->subscription_history);
        return $this->success($data, "Subscription histories");
    }

    public static function creditAffiliate($referrer, $amount, $user)
    {
        if(!$referrer) {
            return;
        }

        $wallet = $referrer->wallet()->firstOrCreate(
            [
                'user_id' => $referrer->id,
            ],
            [
                'balance' => 0.00,
                'reward_point' => 0,
            ]
        );

        $convertAmount = currencyConvert($user->default_currency, $amount, $referrer->default_currency);

        $subcriptionBonus = $convertAmount * 0.05;
        $wallet->increment('balance', $subcriptionBonus);
    }
}




