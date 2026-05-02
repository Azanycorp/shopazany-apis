<?php

namespace App\Services;

use App\Enum\PaymentType;
use App\Enum\PlanType;
use App\Http\Resources\SubscriptionHistoryResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Payment\AuthorizeNetSubscriptionPaymentProcessor;
use App\Services\Payment\HandlePaymentService;
use App\Services\Payment\PaymentDetailsService;
use App\Services\Payment\PaystackPaymentProcessor;
use App\Trait\HttpResponse;
use Illuminate\Auth\AuthManager;

class SubscriptionService
{
    use HttpResponse;

    public function __construct(private readonly AuthManager $authManager) {}

    public function getPlanByCountry($request, $countryId)
    {
        $type = $request->query('type', PlanType::B2C);

        $plan = SubscriptionPlan::where('country_id', $countryId)
            ->where('type', $type)
            ->orderBy('tier')
            ->get();

        $data = SubscriptionPlanResource::collection($plan);

        return $this->success($data, 'Subscription plans');
    }

    public function subscriptionPayment($request)
    {
        [$processor, $details] = match ($request->type) {
            PaymentType::PAYSTACK => [
                new PaystackPaymentProcessor,
                PaymentDetailsService::paystackSubcriptionPayDetails($request),
            ],
            PaymentType::B2B_PAYSTACK => [
                new AuthorizeNetSubscriptionPaymentProcessor,
                PaymentDetailsService::authorizeNetSubcriptionPayDetails($request),
            ],
            default => throw new \InvalidArgumentException(
                "Unsupported type: {$request->type}"
            ),
        };

        $paymentService = new HandlePaymentService($processor);

        if (isset($details['error'])) {
            return $this->error(null, $details['error'], 400);
        }

        return $paymentService->process($details);
    }

    public function subscriptionHistory($userId)
    {
        $currentUserId = $this->authManager->id();

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
        $subcriptionBonus = round($convertedAmount * 0.10, 2);
        $wallet->increment('balance', $subcriptionBonus);
    }
}
