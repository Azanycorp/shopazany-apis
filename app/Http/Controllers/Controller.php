<?php

namespace App\Http\Controllers;

use App\Actions\UserLogAction;
use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserStatus;
use App\Exports\B2BProductExport;
use App\Exports\ProductExport;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

abstract class Controller
{
    use HttpResponse;

    public function __construct(
        private readonly \Illuminate\Foundation\Application $application,
        private readonly \Illuminate\Filesystem\FilesystemManager $filesystemManager,
        private readonly \Illuminate\Routing\UrlGenerator $urlGenerator,
    ) {}

    public function generateUniqueReferrerCode()
    {
        do {
            $referrer_code = Str::random(10);
        } while (User::where('referrer_code', $referrer_code)->exists());

        return $referrer_code;
    }

    public function generateAlternateReferrerCode()
    {
        return strrev(Str::random(6).rand(4, 9876));
    }

    public function getUserReferrer($user)
    {
        if ($user->referrer_code !== null) {
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
        if ($this->application->environment('production')) {
            return "/prod/document/{$email}";
        }

        return "/stag/document/{$email}";
    }

    protected function storeFile($file, string $folder, \Illuminate\Http\Request $request): string
    {
        return uploadImage($request, $file, $folder);
    }

    protected function exportB2bProduct(string $userId, $data)
    {
        $fileName = 'products_'.time().'.xlsx';
        $path = 'public';

        if ($this->application->environment('production')) {
            $folderPath = 'prod/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';

        } elseif ($this->application->environment('staging')) {
            $folderPath = 'stag/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';
        }

        Excel::store(new B2BProductExport($userId, $data), $fileName, $path);

        $fileUrl = ($path === 's3') ? $this->filesystemManager->disk('s3')->url($fileName) : $this->urlGenerator->asset('storage/'.$fileName);

        return $this->success(['file_url' => $fileUrl], 'Product export successful.');
    }

    protected function exportProduct(string $userId)
    {
        $fileName = 'products_'.time().'.xlsx';
        $path = 'public';

        if ($this->application->environment('production')) {
            $folderPath = 'prod/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';

        } elseif ($this->application->environment('staging')) {
            $folderPath = 'stag/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';
        }

        Excel::store(new ProductExport($userId), $fileName, $path);

        $fileUrl = ($path === 's3') ? $this->filesystemManager->disk('s3')->url($fileName) : $this->urlGenerator->asset('storage/'.$fileName);

        return $this->success(['file_url' => $fileUrl], 'Product export successful.');
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
        $status = $status ?? 'pending';
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
            return $this->error(null, 'Please wait a few minutes before requesting a new code.', 400);
        }

        $code = generateVerificationCode();
        $time = now()->addMinutes(10);

        $user->update([
            'login_code' => $code,
            'login_code_expires_at' => $time,
        ]);

        $type = MailingEnum::LOGIN_OTP;
        $subject = 'Login OTP';
        $mail_class = "App\Mail\LoginVerifyMail";

        mailSend($type, $user, $subject, $mail_class);

        $description = "Attempt to login by {$request->email}";
        $response = $this->success(null, 'Code has been sent to your email address.');
        $action = UserLog::LOGIN_ATTEMPT;

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }

    protected function logUserIn($user, $request)
    {
        $user->tokens()->delete();
        $token = $user->createToken('API Token of '.$user->email);

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
        $fileName = 'products_'.time().'.xlsx';
        $path = 'public';

        if ($this->application->environment('production')) {
            $folderPath = 'prod/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';

        } elseif ($this->application->environment('staging')) {
            $folderPath = 'stag/exports/user_'.$userId.'/';
            $fileName = $folderPath.'products_'.time().'.xlsx';
            $path = 's3';
        }

        $data = null;
        Excel::store(new B2BProductExport($userId, $data), $fileName, $path);
        $fileUrl = ($path === 's3') ? $this->filesystemManager->disk('s3')->url($fileName) : $this->urlGenerator->asset('storage/'.$fileName);

        return $this->success(['file_url' => $fileUrl], 'Product export successful.');
    }
}
