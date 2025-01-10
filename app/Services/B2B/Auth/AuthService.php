<?php

namespace App\Services\B2B\Auth;

use App\Models\User;
use App\Enum\UserLog;
use App\Enum\UserType;
use App\Enum\UserStatus;
use App\Models\B2bCompany;
use App\Trait\HttpResponse;
use App\Mail\UserWelcomeMail;
use App\Mail\SignUpVerifyMail;
use App\Actions\SendEmailAction;
use Illuminate\Support\Facades\DB;
use App\Services\Auth\LoginService;

class AuthService
{
    use HttpResponse;

    public function login($request)
    {
        return LoginService::AuthLogin($request);
    }

    public function signup($request)
    {
        $request->validated($request->all());
        $user = null;

        try {

            $code = generateVerificationCode();

            $user = User::create([
                'email' => $request->email,
                'type' => UserType::B2B_SELLER,
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'password' => bcrypt($request->password)
            ]);
            // $user->businessInformation()->create([
            //     'business_name' => $request->business_name,
            //     'business_phone' => $request->business_phone,
            //     'country_id' => $request->country_id,
            //     'city' => $request->city,
            //     'zip' => $request->postal_code,
            //     'address' => $request->address,
            //     'state' => $request->state,
            // ]);
            $description = "User with email: {$request->email} signed up as b2b seller";
            $response = $this->success(null, "Created successfully");
            $action = UserLog::CREATED;

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        } catch (\Exception $e) {
            $description = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), 500);
            $action = UserLog::FAILED;

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }

    public function verify($request)
    {
        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->first();

        if (! $user) {
            return $this->error(null, "Invalid code", 404);
        }

        $user->update([
            'is_verified' => 1,
            'is_admin_approve' => 1,
            'verification_code' => null,
            'email_verified_at' => now(),
            'status' => UserStatus::ACTIVE,
        ]);

        (new SendEmailAction($user->email, new UserWelcomeMail($user)))->run();

        $description = "User with email address {$request->email} verified OTP";
        $action = UserLog::CREATED;
        $response = $this->success(['user_id' => $user->id], "Verified successfully");

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    public function resendCode($request)
    {
        $user = User::getUserEmail($request->email);

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        if ($user->email_verified_at !== null && $user->status === UserStatus::ACTIVE) {
            return $this->error(null, "Account has been verified", 400);
        }

        try {

            $code = generateVerificationCode();

            $user->update([
                'email_verified_at' => null,
                'verification_code' => $code,
            ]);

            defer(fn() => send_email($request->email, new SignUpVerifyMail($user)));

            $description = "User with email address {$request->email} has requested a code to be resent.";
            $action = UserLog::CODE_RESENT;
            $response = $this->success(null, "Code resent successfully");

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

        DB::beginTransaction();

        try {
            $code = generateVerificationCode();

            $user = User::create([
                'first_name' => $request->name,
                'email' => $request->email,
                'type' => UserType::B2B_BUYER,
                'service_type' => $request->service_type,
                'average_spend' => $request->average_spend,
                'company_name' => $request->company_name,
                'company_size' => $request->company_size,
                'website' => $request->website,
                'country' => $request->country_id,
                'email_verified_at' => null,
                'verification_code' => $code,
                'is_verified' => 0,
                'password' => bcrypt($request->password)
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
            $response = $this->success(null, "Created successfully");
            $action = UserLog::CREATED;

            logUserAction($request, $action, $description, $response, $user);
            DB::commit();

            return $this->success($user, "Created successfully");;
        } catch (\Exception $e) {
            DB::rollBack();

            $description = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), 500);
            $action = UserLog::FAILED;

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }
}
