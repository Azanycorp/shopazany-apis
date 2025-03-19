<?php

namespace App\Services\Admin;

use Carbon\Carbon;
use App\Models\User;
use App\Enum\UserStatus;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class AffiliateService
{
    use HttpResponse;

    public function overview()
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

        $affiliateData = DB::table('transactions')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT user_id) as total_affiliate,
                SUM(amount) as total_affiliate_earning,
                SUM(CASE WHEN type = "withdrawal" THEN amount ELSE 0 END) as total_affiliate_withdraw,
                COUNT(CASE WHEN status = "pending" AND type = "withdrawal" THEN id END) as total_affiliate_withdraw_request,
                SUM(CASE WHEN status = "pending" AND type = "withdrawal" THEN amount ELSE 0 END) as total_affiliate_withdraw_request_amount
            ')
            ->first();

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
            ->whereIn('id', function ($query) {
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
                    'percentage' => round(($users->count() / User::whereIn('id', function ($query) {
                        $query->select('referee_id')->from('referral_relationships');
                    })->count()) * 100, 2),
                ];
            })
            ->sortByDesc('referred')
            ->values();

        $data = [
            'total_paid_out' => $affiliateData->total_affiliate_withdraw ?? 0,
            'active_affiliates' => $activeAffiliates,
            'inactive_affiliates' => $inactiveAffiliates,
            'top_affiliates' => $topAffiliates,
            'top_regions' => $topCountries,
        ];

        return $this->success($data, 'Affiliate Overview');
    }

    public function allUsers(): array
    {
        $topAffiliates = User::where('is_affiliate_member', 1)
            ->withCount('referrals')
            ->leftJoin('wallets', 'wallets.user_id', '=', 'users.id')
            ->select([
                'users.*',
                'wallets.balance as wallet_balance',
            ])
            ->orderByDesc('wallets.balance')
            ->paginate(25);

        $data = $topAffiliates->map(function ($user): array {
            return [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'earnings' => $user->wallet_balance ?? 0,
                'referred' => $user->referrals_count ?? 0,
                'referrer_code' => $user->referrer_code,
                'referrer_link' => $user->referrer_link,
                'status' => $user->status,
            ];
        });

        return [
            'status' => true,
            'message' => 'Affiliate Users',
            'data' => $data,
            'pagination' => [
                'current_page' => $topAffiliates->currentPage(),
                'last_page' => $topAffiliates->lastPage(),
                'per_page' => $topAffiliates->perPage(),
                'prev_page_url' => $topAffiliates->previousPageUrl(),
                'next_page_url' => $topAffiliates->nextPageUrl(),
            ],
        ];
    }

    public function userDetail($id)
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
            'referrals' => $user->referrals->map(function ($referral): array {
                return [
                    'id' => $referral->id,
                    'first_name' => $referral->first_name,
                    'last_name' => $referral->last_name,
                    'email' => $referral->email,
                    'status' => $referral->status,
                    'subscription_status' => $referral->subscription_status,
                    'joined' => $referral->created_at,
                    'platform' => 'B2C'
                ];
            }),
        ];

        return $this->success($data, 'Affiliate User Detail');
    }

    public function suspend($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => UserStatus::SUSPENDED,
        ]);

        return $this->success(null, 'User Suspended');
    }

    public function resetPassword($request)
    {
       $user = User::find($request->user_id);
        if(!$user) {
            return $this->error(null,"User not found", 404);
        }

        $status = Password::broker('users')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 500);
    }

}
