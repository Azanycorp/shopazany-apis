<?php

namespace App\Services\B2B\Auth;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Models\User;
use App\Services\Auth\HttpService;
use App\Services\Auth\LoginService;
use App\Trait\HttpResponse;
use App\Trait\SignUp;

class AuthService
{
    use HttpResponse, SignUp;

    public function __construct(private readonly \Illuminate\Database\DatabaseManager $databaseManager, private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher, private HttpService $httpService) {}

    public function login($request)
    {
        return LoginService::AuthLogin($request);
    }

    // public function signup($request)
    // {
    //     $request->validated($request->all());
    //     $user = null;
    //     $headerName = config('security.header_key');
    //     $headerValue = $request->header($headerName);

    //     $requestData = $request->only([
    //         'first_name',
    //         'last_name',
    //         'email',
    //         'password'
    //     ]);

    //     $requestData['signed_up_from'] = 'azany_b2b';
    //     $requestData['type'] = 'b2b_seller';

    //     $response = $this->httpService->register(
    //         $requestData,
    //         [
    //             $headerName => $headerValue
    //         ]
    //     );
    //         if ($response->failed()) {
    //             return $this->error(null, $response['message'], 400);
    //         }

    //     try {
    //         $code = generateVerificationCode();
    //         $user = User::create([
    //             'email' => $request->email,
    //             'type' => UserType::B2B_SELLER,
    //             'email_verified_at' => null,
    //             'verification_code' => $code,
    //             'is_verified' => 0,
    //             'info_source' => $request->info_source ?? null,
    //             'password' => $this->bcryptHasher->make($request->password),
    //         ]);
    //         if ($request->referrer_code) {
    //             $affiliate = User::with('wallet')
    //                 ->where(['referrer_code' => $request->referrer_code, 'is_affiliate_member' => 1])
    //                 ->first();

    //             if (! $affiliate) {
    //                 return $this->error(null, 'No Affiliate found!', 404);
    //             }

    //             $this->handleReferrers($request->referrer_code, $user);
    //         }

    //         $description = "User with email: {$request->email} signed up as b2b seller";
    //         $response = $this->success(null, 'Created successfully', 201);
    //         $action = UserLog::CREATED;

    //         logUserAction($request, $action, $description, $response, $user);

    //         return $response;
    //     } catch (\Exception $e) {
    //         $description = "Sign up failed: {$request->email}";
    //         $response = $this->error(null, $e->getMessage(), 500);
    //         $action = UserLog::FAILED;

    //         logUserAction($request, $action, $description, $response, $user);

    //         return $response;
    //     }
    // }

    public function signup($request)
    {
        $request->validated();

        $headerName = config('security.header_key');
        $headerValue = $request->header($headerName);

        $payload = $this->externalPayload($request);

        $externalResponse = $this->httpService->register(
            $payload,
            [$headerName => $headerValue]
        );

        if ($externalResponse->failed()) {
            return $this->error(null, $externalResponse['message'], 400);
        }

        try {
            $user = $this->createLocalUser($request);
            if ($request->filled('referrer_code')) {
                $refResponse = $this->handleReferral($request, $user);

                if ($refResponse !== true) {
                    return $refResponse;
                }
            }

            $message = "User with email: {$request->email} signed up as b2b seller";
            $response = $this->success(null, 'Created successfully', 201);

            logUserAction($request, UserLog::CREATED, $message, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $message = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, UserLog::FAILED, $message, $response);

            return $response;
        }
    }

    protected function externalPayload($request)
    {
        return [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password,
            'signed_up_from' => 'azany_b2b',
            'type' => 'b2b_seller',
        ];
    }

    protected function createLocalUser($request)
    {
        return User::create([
            'email' => $request->email,
            'type' => UserType::B2B_SELLER,
            'email_verified_at' => null,
            'verification_code' => generateVerificationCode(),
            'is_verified' => 0,
            'info_source' => $request->info_source ?? null,
            'password' => $this->bcryptHasher->make($request->password),
        ]);
    }

    protected function handleReferral($request, $user)
    {
        $affiliate = User::with('wallet')
            ->where('referrer_code', $request->referrer_code)
            ->where('is_affiliate_member', 1)
            ->first();

        if (! $affiliate) {
            return $this->error(null, 'No Affiliate found!', 404);
        }

        $this->handleReferrers($request->referrer_code, $user);

        return true;
    }

    public function verify($request)
    {
        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        if (! $user) {
            return $this->error(null, 'Invalid code', 404);
        }

        $user->update([
            'is_verified' => 1,
            'is_admin_approve' => 1,
            'verification_code' => null,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE,
        ]);

        $type = MailingEnum::EMAIL_VERIFICATION;
        $subject = 'Email verification';
        $mail_class = "App\Mail\UserWelcomeMail";
        $data = [
            'user' => $user,
        ];
        mailSend($type, $user, $subject, $mail_class, $data);

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success(['user_id' => $user->id], 'Verified successfully');

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    public function resendCode($request)
    {
        $user = User::getUserEmail($request->email);

        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        if ($user->email_verified_at !== null && $user->status === UserStatus::ACTIVE) {
            return $this->error(null, 'Account has been verified', 400);
        }

        try {
            $code = generateVerificationCode();

            $user->update([
                'email_verified_at' => null,
                'verification_code' => $code,
            ]);

            $type = MailingEnum::RESEND_CODE;
            $subject = 'Resend code';
            $mail_class = "App\Mail\SignUpVerifyMail";
            $data = [
                'user' => $user,
            ];
            mailSend($type, $user, $subject, $mail_class, $data);

            $description = "User with email address {$request->email} has requested a code to be resent.";
            $action = UserLog::CODE_RESENT;
            $response = $this->success(null, 'Code resent successfully');

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $description = "An error occured during the request email: {$request->email}";
            $action = UserLog::FAILED;
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }

    public function buyerOnboarding($request)
    {
        $user = null;
        $this->databaseManager->beginTransaction();

        try {
            $code = generateVerificationCode();
            $currencyCode = $this->currencyCode($request);

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'type' => UserType::B2B_BUYER,
                'service_type' => $request->service_type,
                'average_spend' => $request->average_spend,
                'company_name' => $request->company_name,
                'company_size' => $request->company_size,
                'website' => $request->website,
                'country' => $request->country_id,
                'default_currency' => $currencyCode,
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'password' => $this->bcryptHasher->make($request->password),
            ]);

            $user->b2bCompany()->create([
                'service_type' => $request->service_type,
                'average_spend' => $request->average_spend,
                'business_name' => $request->company_name,
                'company_size' => $request->company_size,
                'website' => $request->website,
                'country_id' => $request->country_id,
            ]);

            $description = "User with email: {$request->email} signed up as b2b buyer";
            $response = $this->success(null, 'Created successfully');
            $action = UserLog::CREATED;

            logUserAction($request, $action, $description, $response, $user);
            $this->databaseManager->commit();

            return $this->success($user, 'Created successfully');
        } catch (\Exception $e) {
            $this->databaseManager->rollBack();

            $description = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), 500);
            $action = UserLog::FAILED;
            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }
}
