<?php

namespace App\Services\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;

class UserService extends Controller
{
    use HttpResponse;

    public function profile()
    {
        $auth = $this->userAuth();
        $user = User::with(['wallet', 'referrals', 'bankAccount'])->findOrFail($auth->id);
        $data = new ProfileResource($user);

        return $this->success($data, "Profile");
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
                'previous_balance' => $wallet->balance,
                'current_balance' => $current
            ]);

            $wallet->update([
                'balance' => $current
            ]);

            return $this->success(null, "Request sent successfully");
        } else {
            return $this->error(null, "Sorry you can't withdraw above your balance", 400);
        }
    }
}



