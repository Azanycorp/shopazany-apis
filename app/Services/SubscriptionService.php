<?php

namespace App\Services;

use App\Enum\PaymentType;
use App\Http\Resources\SubscriptionHistoryResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Payment\AuthorizeNetSubscriptionPaymentProcessor;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaystackPaymentProcessor;
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

        return $this->success($data, 'Subscription plans');
    }

    public function subscriptionPayment($request)
    {
        $paymentProcessor = match ($request->type) {
            PaymentType::PAYSTACK => new PaystackPaymentProcessor,
            PaymentType::AUTHORIZE => new AuthorizeNetSubscriptionPaymentProcessor,
            default => throw new \Exception('Unsupported payment method'),
        };

        $paymentService = new HandlePaymentService($paymentProcessor);

        $paymentDetails = match ($request->type) {
            PaymentType::PAYSTACK => PaymentDetailsService::paystackSubcriptionPayDetails($request),
            PaymentType::AUTHORIZE => PaymentDetailsService::authorizeNetSubcriptionPayDetails($request),
            default => throw new \Exception('Unsupported payment method'),
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
            return $this->error(null, 'Unauthorized action.', 401);
        }

        $user = User::with(['userSubscriptions.subscriptionPlan'])
            ->findOrFail($userId)
            ->append('subscription_history');

        $data = SubscriptionHistoryResource::collection($user->subscription_history);

        return $this->success($data, 'Subscription histories');
    }

    public static function creditAffiliate($referrer, $amount, $currency): void
    {
        if (! $referrer) {
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

        $convertedAmount = currencyConvert($currency, $amount, $referrer->default_currency);
        $subcriptionBonus = round($convertedAmount * 0.05, 2);
        $wallet->increment('balance', $subcriptionBonus);
    }
}
