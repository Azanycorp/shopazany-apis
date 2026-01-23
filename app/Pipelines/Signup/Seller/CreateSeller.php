<?php

namespace App\Pipelines\Signup\Seller;

use App\Enum\UserLog;
use App\Enum\UserType;
use App\Models\User;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Symfony\Component\HttpFoundation\Response;

class CreateSeller
{
    use HttpResponse, SignUp;

    public function __construct(
        private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher,
    ) {}

    public function handle($request)
    {
        $user = null;

        $currencyCode = currencyCodeByCountryId($request->country_id);
        $coupon = $request->query('coupon');
        $coupon = $this->normalizeCoupon($coupon);
        $referrer = $request->query('referrer') ?? $request->input('referrer_code');

        if (filled($coupon)) {
            try {
                $this->validateCoupon($coupon);
            } catch (\Exception $e) {
                return $this->error(null, $e->getMessage(), 400);
            }
        }

        if (filled($referrer)) {
            try {
                $this->validateReferrerCode((string) $referrer);
            } catch (\Exception $e) {
                return $this->error(null, $e->getMessage(), 400);
            }
        }

        try {
            $code = generateVerificationCode();

            $user = User::create([
                'first_name' => $request->string('first_name'),
                'last_name' => $request->string('last_name'),
                'middlename' => $request->string('other_name'),
                'company_name' => $request->string('business_name'),
                'email' => $request->string('email'),
                'address' => $request->string('address'),
                'country' => $request->string('country_id'),
                'state_id' => $request->string('state_id'),
                'type' => UserType::SELLER,
                'default_currency' => $currencyCode,
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'hear_about_us' => $request->string('hear_about_us'),
                'password' => $this->bcryptHasher->make($request->string('password')),
            ]);

            if (filled($coupon)) {
                $this->assignCoupon($coupon, $user);
            }

            if (filled($referrer)) {
                $user->update(['pending_referrer_code' => $referrer]);
            }

            $description = "Seller with email address {$request->email} just signed up";
            $action = UserLog::CREATED;
            $response = $this->success(null, 'Created successfully');

            logUserAction($request, $action, $description, $response, $user);

            return $this->success(null, 'Created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $description = "Sign up error for user with email {$request->email}";
            $action = UserLog::FAILED;
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }
}
