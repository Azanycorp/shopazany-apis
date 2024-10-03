<?php

namespace App\Services;

use App\Enum\PaymentType;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Trait\HttpResponse;
use Unicodeveloper\Paystack\Facades\Paystack;

class SubscriptionService
{
    use HttpResponse;

    public function getPlanByCountry($countryId)
    {
        $plan = SubscriptionPlan::where('country_id', $countryId)->get();
        $data = SubscriptionPlanResource::collection($plan);

        return $this->success($data, "Subscription plan");
    }

    public function subscriptionPayment($request)
    {
        $amount = $request->input('amount') * 100;

        $callbackUrl = $request->input('redirect_url');
        if (!filter_var($callbackUrl, FILTER_VALIDATE_URL)) {
            return $this->error(null, 'Invalid callback URL', 400);
        }

        $user = User::findOrFail($request->user_id);

        if($user->user_subscribe) {
            return $this->error(null, 'Subscription is active', 403);
        }

        $paymentDetails = [
            'email' => $request->input('email'),
            'amount' => $amount,
            'currency' => 'NGN',
            'metadata' => json_encode([
                'user_id' => $request->input('user_id'),
                'subscription_plan_id' => $request->input('subscription_plan_id'),
                'payment_method' => 'paystack',
                'payment_type' => PaymentType::RECURRINGCHARGE,
            ]),
            'callback_url' => $callbackUrl
        ];

        $paystackInstance = Paystack::getAuthorizationUrl($paymentDetails);
        return response()->json($paystackInstance);
    }
}




