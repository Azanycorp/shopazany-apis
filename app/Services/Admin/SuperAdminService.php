<?php

namespace App\Services\Admin;

use App\Enum\MailingEnum;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use App\Mail\AdminUserMail;
use App\Mail\ChangePasswordMail;
use App\Http\Resources\SuperAdminProfileResource;
use App\Models\Admin;

class SuperAdminService
{
    use HttpResponse;

    public function clearCache()
    {
        Artisan::call('optimize:clear');
        return response()->json(['message' => 'cached files cleared!']);
    }

    public function runMigration()
    {
        Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'message' => 'Migration completed successfully.',
            'output' => Artisan::output(),
        ]);
    }

    public function seedRun()
    {
        $seederClass = Str::studly(request()->input('seeder_class'));

        if (!class_exists("Database\\Seeders\\{$seederClass}")) {
            return response()->json([
                'error' => "Seeder class '{$seederClass}' not found in Database\\Seeders namespace."
            ], 404);
        }

        try {
            Artisan::call('db:seed', [
                '--class' => $seederClass,
                '--force' => true,
            ]);

            return response()->json([
                'message' => "{$seederClass} executed successfully.",
                'output' => Artisan::output(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Seeder failed to run.',
                'details' => $e->getMessage(),
            ], 400);
        }
    }

    public function getProfiles()
    {
        $users = Admin::all();

        $data = SuperAdminProfileResource::collection($users);

        return $this->success($data, "Profiles");
    }

    public function getProfile($userId)
    {
        $user = Admin::findOrFail($userId);

        $data = new SuperAdminProfileResource($user);

        return $this->success($data, "Profile");
    }

    public function addUser($request)
    {
        $password = Str::random(8);
        $admin = Admin::create(
            $request->validated()
            + ['password' => $password]
        );

        $type = MailingEnum::ADMIN_ACCOUNT;
        $subject = 'Admin Account Created';
        $mail_class = AdminUserMail::class;
        $data = [
            'user' => $admin,
            'pass' => $password,
        ];
        mailSend($type, $admin, $subject, $mail_class, $data);

        return $this->success(null, "User added successfully", 201);
    }

    public function security($request)
    {
        $user = Admin::findOrFail($request->user_id);

        if ($request->has('two_factor')) {
            $user->update(['two_factor_enabled' => $request->two_factor]);

            return $this->success(null, "Two-factor updated successfully");
        }

        if ($request->has('change_password')) {
            $code = Str::random(6);
            $user->update([
                'verification_code' => $code,
                'verification_code_expire_at' => now()->addMinutes(15),
            ]);

            $type = MailingEnum::EMAIL_VERIFICATION;
            $subject = 'Email Verification';
            $mail_class = ChangePasswordMail::class;
            $data = [
                'user' => $user,
                'code' => $code,
            ];
            mailSend($type, $user, $subject, $mail_class, $data);

            return $this->success(null, "Verification code sent successfully");
        }
    }

    public function verifyCode($request)
    {
        $user = Admin::where('verification_code', $request->code)
            ->where('verification_code_expire_at', '>', now())
            ->first();

        if (! $user) {
            return $this->error(null, 'Invalid code', 404);
        }
        
        $user->update([
            'verification_code' => null,
            'verification_code_expire_at' => null,
        ]);

        return $this->success(null, "Code verified successfully");
    }

    public function changePassword($request)
    {
        $user = Admin::findOrFail($request->user_id);

        $user->update(['password' => $request->password]);

        return $this->success(null, "Password changed successfully");
    }
}
