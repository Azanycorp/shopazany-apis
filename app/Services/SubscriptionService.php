<?php

namespace App\Services;

use App\Enum\PaymentType;
use App\Enum\UserStatus;
use App\Http\Resources\SubscriptionHistoryResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;
use Unicodeveloper\Paystack\Facades\Paystack;

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
        $amount = $request->input('amount') * 100;

        $callbackUrl = $request->input('redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return $this->error(null, 'Invalid callback URL', 400);
        }

        $user = User::with([
            'referrer' => function ($query) {
                $query->with('wallet');
            },
            'userSubscriptions' => function ($query) {
                $query->where('status', UserStatus::ACTIVE);
            }
        ])->findOrFail($request->user_id);

        if($user->is_subscribed) {
            $currentPlan = $user?->subscription_plan?->subscriptionPlan;
            $newPlan = SubscriptionPlan::findOrFail($request->input('subscription_plan_id'));

            if ($newPlan->tier < $currentPlan->tier) {
                return $this->error(null, 'You cannot downgrade your subscription plan', 403);
            }
            if ($newPlan->id == $currentPlan->id) {
                return $this->error(null, 'You are already subscribed to this plan', 403);
            }
        }

        $paymentDetails = [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => 'NGN',
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'referrer_id' => $user->referrer->first()?->id,
                'subscription_plan_id' => $request->input('subscription_plan_id'),
                'payment_method' => 'paystack',
                'payment_type' => PaymentType::RECURRINGCHARGE,
            ]),
            'callback_url' => $callbackUrl
        ];

        $paystackInstance = Paystack::getAuthorizationUrl($paymentDetails);
        return response()->json($paystackInstance);
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




