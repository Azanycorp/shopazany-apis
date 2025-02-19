<?php

namespace App\Trait;

use App\Models\Bank;
use App\Models\User;
use App\Services\Payment\PaystackService;
use Illuminate\Support\Facades\DB;

trait Payment
{
    protected function addBankTransfer($request, User $user)
    {
        return match ($request->platform) {
            'paystack' => $this->addPaystackMethod($request, $user),
        };
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

    private function addPaystackMethod($request, User $user)
    {
        try {
            DB::beginTransaction();

            $method = $user->paymentMethods()->create([
                'type' => $request->type,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name
            ]);

            $bank = Bank::where([
                'name' => $request->bank_name
            ])->first();

            if(! $bank) {
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
}
