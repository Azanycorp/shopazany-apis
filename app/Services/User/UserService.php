<?php

namespace App\Services\User;

use App\Enum\TransactionStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\TransactionResource;
use App\Models\BankAccount;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Services\TransactionService;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;

class UserService extends Controller
{
    use HttpResponse;

    public function profile()
    {
        $auth = $this->userAuth();
        $user = User::with(['wallet', 'referrals', 'bankAccount', 'userbusinessinfo'])
        ->findOrFail($auth->id);
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

        if (!$user) {
            return $this->error(null, "User not found", 404);
        }

        $image = $request->hasFile('image') ? uploadUserImage($request, 'image', $user) : $user->image;

        $user->update([
            'first_name' => $request->first_name ?? $user->first_name,
            'last_name' => $request->last_name ?? $user->last_name,
            'middlename' => $request->middlename ?? $user->middlename,
            'address' => $request->address ?? $user->address,
            'phone' => $request->phone_number ?? $user->phone,
            'country' => $request->country_id ?? $user->country,
            'state_id' => $request->state_id ?? $user->state_id,
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
        $user = User::with('wallet')->find($request->user_id);

        if(!$user){
            return $this->error(null, "User not found", 404);
        }

        $wallet = $user->wallet;

        if(!$wallet){
            return $this->error(null, "Wallet not found", 404);
        }

        if($wallet->balance >= $request->amount){
            $current = $wallet->balance - $request->amount;

            WithdrawalRequest::create([
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'previous_balance' => $wallet?->balance,
                'current_balance' => $current
            ]);

            $wallet->update([
                'balance' => $current
            ]);

            (new TransactionService($user, 'withdrawal', $request->amount))->logTransaction();

            return $this->success(null, "Request sent successfully");
        } else {
            return $this->error(null, "Sorry you can't withdraw above your balance", 400);
        }
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
                $path = 'kyc' . '/' . $name;
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

        $user = User::with(['wallet', 'withdrawalRequest'])
        ->find($id);

        if(! $user) {
            return $this->error(null, "User not found", 404);
        }

        $pending = $user->withdrawalRequest->where('status', 'pending')->sum('amount');

        $data = [
            'current_balance' => $user?->wallet?->balance,
            'pending_withdrawals' => $pending,
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
        $query = Transaction::where('user_id', $userId);

        if ($status) {
            if (!in_array($status, [TransactionStatus::SUCCESSFUL, TransactionStatus::PENDING, TransactionStatus::REJECTED])) {
                return $this->error(null, "Invalid status", 400);
            }

            $query->where('status', $status);
        }

        $trnx = $query->get();
        $data = TransactionResource::collection($trnx);

        return $this->success($data, "Transaction history");
    }

    public function addPaymentMethod($request)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $request->user_id) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('paymentMethods')->find($request->user_id);

        if(! $user){
            return $this->error(null, "User not found", 404);
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

        if ($methodAdded) {
            return $this->success(null, "Added successfully");
        } else {
            return $this->error(null, "Failed to add payment method", 500);
        }
    }

    public function getPaymentMethod($userId)
    {
        $currentUserId = Auth::id();

        if ($currentUserId != $userId) {
            return $this->error(null, "Unauthorized action.", 401);
        }

        $user = User::with('paymentMethods')->find($userId);

        if(! $user){
            return $this->error(null, "User not found", 404);
        }

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

}



