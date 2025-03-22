<?php

namespace App\Trait;

use App\Models\Bank;
use App\Models\User;
use App\Enum\UserType;
use App\Enum\WithdrawalStatus;
use Illuminate\Support\Facades\DB;
use App\Services\Payment\PaystackService;

trait VerifyPaymentMethod
{
    protected function addBankTransfer($request, User $user)
    {
        return match ($request->platform) {
            'paystack' => $this->addPaystackMethod($request, $user),
            'authorize' => $this->addAuthorizeMethod($request, $user),
        };
    }

    protected function addPayPal($request, User $user): bool
    {
        try {
            $user->B2bWithdrawalMethod()->create([
                'type' => $request->type,
                'paypal_email' => $request->paypal_email
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function addPaystackMethod($request, User $user)
    {
        try {
            DB::beginTransaction();

            if ($request->is_default) {
                $user->paymentMethods()->update(['is_default' => false]);
            }
            $method = $user->B2bWithdrawalMethod()->create([
                'type' => $request->type,
                'country_id' => $request->country_id,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'platform' => $request->platform,
                'status' => WithdrawalStatus::ACTIVE,
            ]);


            $bank = Bank::where([
                'name' => $request->bank_name
            ])->first();

            if (! $bank) {
                return $this->error(null, "Selected bank not found!", 404);
            }
            $fields = [
                'type' => "nuban",
                'name' => $request->account_name,
                'account_number' => $request->account_number,
                'bank_code' => $bank->code,
                'currency' => $bank->currency
            ];
            PaystackService::createRecipient($fields, $method);

            DB::commit();
            return $this->success(null, "Added successfully");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function addAuthorizeMethod($request, User $user)
    {
        if ($request->is_default) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $user->paymentMethods()->create([
            'type' => $request->type,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'platform' => $request->platform,
            'routing_number' => $request->routing_number,
            'is_default' => $request->is_default,
        ]);
        return $this->success(null, "Added successfully");
    }
}
