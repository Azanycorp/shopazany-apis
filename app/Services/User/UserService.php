<?php

namespace App\Services\User;

use App\Enum\TransactionStatus;
use App\Enum\UserStatus;
use App\Enum\UserType;
use App\Enum\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ProfileResource;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\TransactionService;
use App\Trait\HttpResponse;
use App\Trait\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService extends Controller
{
    use HttpResponse, Payment;

    public function profile()
    {
        $auth = $this->userAuth();
        $user = User::with(['wallet', 'bankAccount', 'userbusinessinfo', 'userSubscriptions', 'userShippingAddress'])
            ->withCount('referrals')
            ->findOrFail($auth->id)
            ->append(['is_subscribed', 'subscription_plan']);

        $data = new ProfileResource($user);

        return $this->success($data, "Profile");
    }

    public function updateProfile($request, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->error(null, "User not found", 404);
        }

        $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'middlename' => $request->middlename ?? $user->middlename,
            'company_name' => $request->business_name ?? $user->company_name,
            'address' => $request->address ?? $user->address,
            'phone' => $request->phone_number ?? $user->phone,
            'date_of_birth' => $request->date_of_birth ?? $user->date_of_birth,
            'image' => $image,
        ]);

        return $this->success([
            'user_id' => $user->id
        ], "Updated successfully");
    }

    public function bankAccount($request)
    {
        $user = User::with(['bankAccount'])
        ->find($request->user_id);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        $user->bankAccount()->create([
            'account_name' => $request->account_name,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number
        ]);

        return $this->success(null, "Added successfully");
    }

    public function removeBankAccount($request)
    {
        $account = BankAccount::where('user_id', $request->user_id)->first();

        if(!$account){
            return $this->error(null, "Data not found", 404);
        }

        $account->delete();

        return $this->success(null, "Deleted successfully");
    }

    public function withdraw($request)
    {
        $auth = Auth::user();

        if (!$auth || $auth->type === UserType::CUSTOMER || !$auth->is_affiliate_member || $auth->id !== $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with(['wallet', 'paymentMethods'])
                    ->where('id', $request->user_id)
                    ->first();

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        if ($user->paymentMethods->isEmpty()) {
            return $this->error(null, "No payment method found", 404);
        }

        $wallet = $user->wallet;

        if (!$wallet) {
            return $this->error(null, "Wallet not found", 404);
        }

        if ($wallet->balance < $request->amount) {
            return $this->error(null, "Insufficient balance for withdrawal", 400);
        }

        DB::transaction(function () use ($wallet, $user, $request) {
            $newBalance = $wallet->balance - $request->amount;

            WithdrawalRequest::create([
                'user_id' => $user->id,
                'user_type' => 'b2c_affiliate',
                'amount' => $request->amount,
                'previous_balance' => $wallet->balance,
                'current_balance' => $newBalance,
                'status' => WithdrawalStatus::PENDING,
            ]);

            $wallet->update(['balance' => $newBalance]);

            (new TransactionService($user, TransactionStatus::WITHDRAWAL, $request->amount))->logTransaction();
        });

        return $this->success(null, "Request sent successfully");
    }

    public function userKyc($request)
    {
        $user = User::with('kyc')->find($request->user_id);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        try {
            $parts = explode('@', $user->email);
            $name = $parts[0];

            if($request->file('image')){
                $file = $request->file('image');
                $path = 'kyc/' . $name;
                $filename = time() . rand(10, 1000) . '.' . $file->extension();
                $file->move(public_path($path), $filename, 'public');
                $kycpath = config('services.baseurl') . '/' . $path . '/' . $filename;
            }

            $user->kyc()->create([
                'name' => $request->fullname,
                'date_of_birth' => $request->date_of_birth,
                'nationality' => $request->nationality,
                'country_of_residence' => $request->country_of_residence,
                'city' => $request->city,
                'phone_number' => $request->phone_number,
                'document_number' => $request->document_number,
                'document_type' => $request->document_type,
                'image' => $kycpath
            ]);

            return $this->success(null, "Added successfully");

        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function earningOption($request)
    {
        $user = User::find($request->user_id);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        $user->update([
            'income_type' => $request->type
        ]);

        return $this->success(null, "Added successfully");
    }

    public function dashboardAnalytic($id)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with(['wallet', 'withdrawalRequest', 'paymentMethods'])
            ->find($id);

        if(! $user) {
            return $this->error(null, "User not found", 404);
        }

        $pending = $user->withdrawalRequest->where('status', WithdrawalStatus::PENDING)
            ->sum('amount');

        $data = [
            'current_balance' => $user?->wallet?->balance,
            'pending_withdrawals' => $pending,
            'payment_method' => optional($user?->paymentMethods->where('is_default', 1)->first())->account_number,
        ];

        return $this->success($data, "Dashboard analytics");
    }

    public function transactionHistory($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $status = request()->query('status');
        $perPage = request()->query('per_page', 25);
        $page = request()->query('page', 1);

        $transactionQuery = Transaction::where('user_id', $userId);
        if ($status) {
            if (!in_array($status, [TransactionStatus::SUCCESSFUL, TransactionStatus::PENDING, TransactionStatus::REJECTED])) {
                return $this->error(null, "Invalid status", 400);
            }
            $transactionQuery->where('status', $status);
        }

        $transactions = $transactionQuery->get()->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'transaction_id' => $transaction->id,
                'type' => 'transaction',
                'date' => $transaction->created_at->format('Y-m-d'),
                'amount' => $transaction->amount,
                'status' => $transaction->status,
            ];
        });

        $withdrawalQuery = WithdrawalRequest::where('user_id', $userId);
        if ($status) {
            $withdrawalQuery->where('status', $status);
        }

        $withdrawals = $withdrawalQuery->get()->map(function ($withdrawal) {
            return [
                'id' => $withdrawal->id,
                'transaction_id' => $withdrawal->reference,
                'type' => 'withdrawal',
                'date' => $withdrawal->created_at->format('Y-m-d'),
                'amount' => $withdrawal->amount,
                'status' => $withdrawal->status,
            ];
        });

        $mergedData = collect($transactions)->merge(collect($withdrawals))->sortByDesc('date')->values();

        $total = $mergedData->count();
        $paginatedData = $mergedData->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'status' => 'true',
            'message' => 'Transaction history',
            'data' => $paginatedData,
            'pagination' => [
                'current_page' => (int) $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => (int) $perPage,
                'total' => $total,
                'prev_page_url' => $page > 1 ? request()->url() . '?page=' . ($page - 1) . '&per_page=' . $perPage : null,
                'next_page_url' => $page < ceil($total / $perPage) ? request()->url() . '?page=' . ($page + 1) . '&per_page=' . $perPage : null,
            ],
        ]);
    }

    public function addPaymentMethod($request)
    {
        $auth = Auth::user();

        if (!$auth) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        if($auth->type === UserType::CUSTOMER) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        if ($auth->id !== $request->user_id || !$auth->is_affiliate_member) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('paymentMethods')->find($request->user_id);

        if(! $user){
            return $this->error(null, "User not found", 404);
        }

        if ($user->paymentMethods->count() >= 3) {
            return $this->error(null, "You can only add up to 3 payment methods", 400);
        }

        switch ($request->type) {
            case 'bank_transfer':
                $methodAdded = $this->addBankTransfer($request, $user);
                break;

            case 'paypal':
                $methodAdded = $this->addPayPal($request, $user);
                break;

            default:
                return $this->error(null, "Invalid type", 400);
        }

        return $methodAdded;
    }

    public function getPaymentMethod($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('paymentMethods')
            ->findOrFail($userId);

        $data = PaymentMethodResource::collection($user->paymentMethods);

        return $this->success($data, "Payment methods");
    }

    public function changeSettings($request, $userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::find($userId);

        if(! $user){
            return $this->error(null, "User not found", 404);
        }

        $password = $user->password;

        if($request->password) {
            $password = bcrypt($request->password);
        }

        $user->update([
            'two_factor_enabled' => $request->two_factor_enabled,
            'password' => $password,
        ]);

        return $this->success(null, "Settings changed successfully");
    }

    public function referralManagement($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId !== (int) $userId) {
            return $this->error(null, "Unauthorized action.", 403);
        }

        $searchQuery = request()->query('search');
        $statusFilter = request()->query('status');

        $user = User::with(['referrals' => function ($query) use ($searchQuery, $statusFilter) {
                $query->select(
                    'users.id',
                    'users.first_name',
                    'users.last_name',
                    'users.email',
                    'users.phone',
                    'users.status',
                    'users.created_at'
                )
                ->filterReferrals($searchQuery, $statusFilter);
            }])
            ->withCount('referrals')
            ->find($userId);

        if(! $user){
            return $this->error(null, "User not found", 404);
        }

        $totalSignedUp = $user->referrals()
            ->where('status', UserStatus::ACTIVE)
            ->count();

        $data = [
            'total_referrals' => $user->referrals_count,
            'total_signed_up' => $totalSignedUp,
            'referrals' => $user->referrals ? $user->referrals->map(function ($referral) {
                return [
                    'id' => $referral->id,
                    'name' => $referral->first_name . ' ' . $referral->last_name,
                    'phone' => $referral->phone,
                    'email' => $referral->email,
                    'status' => $referral->status,
                    'referral_date' => $referral->created_at->format('Y-m-d'),
                ];
            })->toArray() : [],
        ];

        return $this->success($data, "Referral management");
    }
}



