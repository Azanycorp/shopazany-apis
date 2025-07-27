<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Enum\AdminType;
use App\Enum\UserStatus;
use App\Enum\AdminStatus;
use App\Enum\MailingEnum;
use App\Mail\AdminUserMail;
use App\Trait\HttpResponse;
use Illuminate\Support\Str;
use App\Enum\WithdrawalStatus;
use App\Mail\ChangePasswordMail;
use App\Trait\SuperAdminNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Password;
use App\Http\Resources\SuperAdminProfileResource;

class SuperAdminService
{
    use HttpResponse, SuperAdminNotification;

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
        $users = Admin::where('type', AdminType::SUPER_ADMIN)->whereNot('id', auth()->user()->id)->latest()->get();

        $data = SuperAdminProfileResource::collection($users);

        return $this->success($data, "Profiles");
    }

    public function getProfile($userId)
    {
        $user = Admin::where('type', AdminType::SUPER_ADMIN)->where('id', $userId)->firstOrFail();

        $data = new SuperAdminProfileResource($user);

        return $this->success($data, "Profile");
    }

    public function deleteAdmin($userId)
    {
        $admin = Admin::where('type', AdminType::SUPER_ADMIN)->where('id', $userId)->firstOrFail();

        $admin->delete();

        return $this->success(null, "Admin deleted");
    }

    public function addUser($request)
    {
        $password = Str::random(8);
        $admin = Admin::create(
            $request->validated()
                + [
                    'type' => AdminType::SUPER_ADMIN,
                    'password' => bcrypt($password),
                    'status' => AdminStatus::ACTIVE,
                ]
        );

        $type = MailingEnum::ADMIN_ACCOUNT;
        $subject = 'Admin Account Created';
        $mail_class = AdminUserMail::class;
        $data = [
            'user' => $admin,
            'pass' => $password,
        ];
        mailSend($type, $admin, $subject, $mail_class, $data);
        $this->createNotification('New Admin Added', 'New admin account created for ' . $admin->fullName);

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
            $code = generateVerificationCode(4);
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

        cache()->put("password_reset_verified_{$user->id}", true, now()->addMinutes(10));

        return $this->success(null, "Code verified successfully");
    }

    public function changePassword($request)
    {
        $user = Admin::findOrFail($request->user_id);

        if (! cache()->pull("password_reset_verified_{$user->id}")) {
            return $this->error(null, 'You must verify the code first', 403);
        }

        $user->update(['password' => bcrypt($request->password)]);

        return $this->success(null, "Password changed successfully");
    }

    //Affiliate section


    public function affiliateOverview()
    {
        $startDate = Carbon::now()->subDays(7)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $activeAffiliates = User::where('is_affiliate_member', 1)
            ->where('status', UserStatus::ACTIVE)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $inactiveAffiliates = User::where('is_affiliate_member', 1)
            ->where('status', '!=', UserStatus::ACTIVE)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalPaidOutInUSD = User::where('is_affiliate_member', 1)
            ->whereHas('transactions', function ($query) use ($startDate, $endDate): void {
                $query->where('type')
                    ->where('status', WithdrawalStatus::COMPLETED)
                    ->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get()
            ->sum(function ($user) use ($startDate, $endDate): float {
                $amount = $user->transactions()
                    ->where('type', 'withdrawal')
                    ->where('status', WithdrawalStatus::COMPLETED)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->sum('amount');

                return currencyConvert($user->default_currency, $amount, 'USD');
            });

        $topAffiliates = User::where('is_affiliate_member', 1)
            ->whereHas('transactions')
            ->withCount('referrals')
            ->withSum('transactions', 'amount')
            ->orderByDesc('transactions_sum_amount')
            ->limit(5)
            ->get()
            ->map(function ($user): array {
                return [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'earnings' => $user->transactions_sum_amount ?? 0,
                    'referred' => $user->referrals_count ?? 0,
                ];
            });

        $topCountries = User::with('userCountry')
            ->whereIn('id', function ($query): void {
                $query->select('referee_id')
                    ->from('referral_relationships');
            })
            ->get()
            ->groupBy('userCountry.id')
            ->map(function ($users, $countryId): array {
                return [
                    'country' => $countryId,
                    'country_name' => optional($users->first()->userCountry)->name,
                    'referred' => $users->count(),
                    'percentage' => round(($users->count() / User::whereIn('id', function ($query): void {
                        $query->select('referee_id')->from('referral_relationships');
                    })->count()) * 100, 2),
                ];
            })
            ->sortByDesc('referred')
            ->values();

        $data = [
            'total_paid_out' => $totalPaidOutInUSD,
            'active_affiliates' => $activeAffiliates,
            'inactive_affiliates' => $inactiveAffiliates,
            'top_affiliates' => $topAffiliates,
            'top_regions' => $topCountries,
        ];

        return $this->success($data, 'Affiliate Overview');
    }


    public function allAffiliates()
    {
        $topAffiliates = User::where('is_affiliate_member', 1)
            ->withCount('referrals')
            ->leftJoin('wallets', 'wallets.user_id', '=', 'users.id')
            ->select([
                'users.*',
                'wallets.balance as wallet_balance',
            ])
            ->orderByDesc('wallets.balance')
            ->paginate(2);

        $topAffiliates->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'earnings' => $user->wallet_balance ?? 0,
                'default_currency' => $user->default_currency,
                'referred' => $user->referrals_count ?? 0,
                'referrer_code' => $user->referrer_code,
                'referrer_link' => $user->referrer_link,
                'status' => $user->status,
            ];
        });

        return $this->withPagination($topAffiliates, 'Affiliate Users');
    }


    public function affiliateDetail($id)
    {
        $user = User::withCount(['referrals'])
            ->withSum('transactions', 'amount')
            ->findOrFail($id);

        $data = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'earnings' => $user->transactions_sum_amount ?? 0,
            'referred' => $user->referrals_count ?? 0,
            'status' => $user->status,
            'referrals' => $user->referrals->map(fn($referral): array => [
                'id' => $referral->id,
                'first_name' => $referral->first_name,
                'last_name' => $referral->last_name,
                'email' => $referral->email,
                'status' => $referral->status,
                'subscription_status' => $referral->subscription_status,
                'joined' => $referral->created_at,
                'platform' => 'B2C',
            ]),
        ];

        return $this->success($data, 'Affiliate User Detail');
    }

    public function suspendAffiliate($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => UserStatus::SUSPENDED,
        ]);

        return $this->success(null, 'User Suspended');
    }

    public function resetAffiliatePassword($request)
    {
        $user = User::find($request->user_id);
        if (! $user) {
            return $this->error(null, 'User not found', 404);
        }

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }
}
