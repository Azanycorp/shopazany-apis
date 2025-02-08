<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Mail\LoginVerifyMail;
use App\Actions\UserLogAction;
use App\Enum\MailingEnum;
use App\Exports\ProductExport;
use App\Exports\B2BProductExport;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    use HttpResponse;

    public function generateUniqueReferrerCode()
    {
        do {
            $referrer_code = Str::random(10);
        } while (User::where('referrer_code', $referrer_code)->exists());

        return $referrer_code;
    }

    public function generateAlternateReferrerCode()
    {
        return strrev(Str::random(6) . rand(4, 9876));
    }

    public function getUserReferrer($user)
    {
        if($user->referrer_code !== null){
            return $this->error(null, 'Account has been created', 403);
        }
        return null;
    }

    protected function userAuth()
    {
        return Auth::user();
    }

    public function logUserAction($request, $action, $description, $response, $user = null): void
    {
        (new UserLogAction($request, $action, $description, $response, $user))->run();
    }

    protected function getStorageFolder(string $email): string
    {
        if (App::environment('production')) {
            return "/prod/document/{$email}";
        }

        return "/stag/document/{$email}";
    }

    protected function storeFile($file, string $folder): string
    {
        $path = $file->store($folder, 's3');
        return Storage::disk('s3')->url($path);
    }

    protected function exportB2bProduct(string $userId,$data)
    {
        $fileName = 'products_' . time() . '.xlsx';
        $path = 'public';

        if(App::environment('production')) {
            $folderPath = 'prod/exports/user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';

        } elseif(App::environment('staging')) {
            $folderPath = 'stag/exports/user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';
        }

        Excel::store(new B2BProductExport($userId,$data), $fileName, $path);

        $fileUrl = ($path === 's3') ? Storage::disk('s3')->url($fileName) : asset('storage/' . $fileName);

        return $this->success(['file_url' => $fileUrl], "Product export successful.");
    }

    protected function exportProduct(string $userId)
    {
        $fileName = 'products_' . time() . '.xlsx';
        $path = 'public';

        if(App::environment('production')) {
            $folderPath = 'prod/exports/user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';

        } elseif(App::environment('staging')) {
            $folderPath = 'stag/exports/user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';
        }

        Excel::store(new ProductExport($userId), $fileName, $path);

        $fileUrl = ($path === 's3') ? Storage::disk('s3')->url($fileName) : asset('storage/' . $fileName);

        return $this->success(['file_url' => $fileUrl], "Product export successful.");
    }

    protected function addBankTransfer($request, User $user): bool
    {
        try {
            $user->paymentMethods()->create([
                'type' => $request->type,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_holder_name' => $request->account_holder_name,
                'swift' => $request->swift,
                'bank_branch' => $request->bank_branch
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function addPayPal($request, User $user): bool
    {
        try {
            $user->paymentMethods()->create([
                'type' => $request->type,
                'paypal_email' => $request->paypal_email
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function isAccountUnverifiedOrInactive($user, $request)
    {
        return $user->email_verified_at === null && $user->verification_code !== null;
    }

    protected function isAccountPending($user, $request)
    {
        return $user->status === UserStatus::PENDING;
    }

    protected function isAccountSuspended($user, $request)
    {
        return $user->status === UserStatus::SUSPENDED;
    }

    protected function isAccountBlocked($user, $request)
    {
        return $user->status === UserStatus::BLOCKED;
    }

    protected function handleAccountIssues($user, $request, $message, $action, $status = null)
    {
        $status = $status ?? "pending";
        $description = "Account issue for user {$request->email}";
        $response = $this->error([
            'id' => $user->id,
            'status' => $status,
        ], $message, 400);

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function handleTwoFactorAuthentication($user, $request)
    {
        if ($user->login_code_expires_at > now()) {
            return $this->error(null, "Please wait a few minutes before requesting a new code.", 400);
        }

        $code = generateVerificationCode();
        $time = now()->addMinutes(10);

        $user->update([
            'login_code' => $code,
            'login_code_expires_at' => $time,
        ]);

        $type = MailingEnum::LOGIN_OTP;
        $subject = "Login OTP";
        $mail_class = "App\Mail\LoginVerifyMail";

        mailSend($type, $user, $subject, $mail_class);

        $description = "Attempt to login by {$request->email}";
        $response = $this->success(null, "Code has been sent to your email address.");
        $action = UserLog::LOGIN_ATTEMPT;

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function logUserIn($user, $request)
    {
        $user->tokens()->delete();
        $token = $user->createToken('API Token of ' . $user->email);

        $description = "User with email {$request->email} logged in";
        $action = UserLog::LOGGED_IN;
        $response = $this->success([
            'user_id' => $user->id,
            'user_type' => $user->type,
            'has_signed_up' => true,
            'is_affiliate_member' => $user->is_affiliate_member === 1,
            'two_factor_enabled' => $user->two_factor_enabled === 1,
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ], 'Login successful.');

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function handleInvalidCredentials($request)
    {
        $description = "Credentials do not match {$request->email}";
        $action = UserLog::LOGIN_ATTEMPT;
        $response = $this->error(null, 'Credentials do not match', 401);

        logUserAction($request, $action, $description, $response);
        return $response;
    }

    protected function b2bExportProduct(string $userId)
    {
        $fileName = 'products_' . time() . '.xlsx';
        $path = 'public';

        if(App::environment('production')) {
            $folderPath = 'prod/exports/user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';

        } elseif(App::environment('staging')) {
            $folderPath = 'stag/exports/user_'. $userId . '/';
            $fileName = $folderPath . 'products_' . time() . '.xlsx';
            $path = 's3';
        }

        $data = null;
        Excel::store(new B2BProductExport($userId, $data), $fileName, $path);
        $fileUrl = ($path === 's3') ? Storage::disk('s3')->url($fileName) : asset('storage/' . $fileName);

        return $this->success(['file_url' => $fileUrl], "Product export successful.");
    }

}
