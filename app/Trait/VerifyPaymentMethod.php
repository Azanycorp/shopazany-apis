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
            'paystack' => $this->addPaymentMethod($request, $user, 'paystack'),
            'authorize' => $this->addPaymentMethod($request, $user, 'authorize'),
        };
    }

    protected function addPayPal($request, User $user): bool
    {
        return $this->createWithdrawalMethod($request, $user, [
            'paypal_email' => $request->paypal_email,
        ]) !== null;
    }

    private function addPaymentMethod($request, User $user, string $platform)
    {
        try {
            DB::beginTransaction();

            if ($request->is_default) {
                $user->paymentMethods()->update(['is_default' => false]);
            }

            $method = $this->createWithdrawalMethod($request, $user, [
                'bank_name' => $request->bank_name ?? null,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
                'type' => $request->account_name,
                'platform' => $platform,
                'is_default' => $request->is_default ?? false,
            ]);

            if ($platform === 'paystack') {
                $this->processPaystackRecipient($request, $method);
            }

            DB::commit();
            return $this->success(null, "Added successfully");
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function createWithdrawalMethod($request, User $user, array $data): ?object
    {
        return $this->getWithdrawalMethodRelation($user)->create($data);
    }

    private function getWithdrawalMethodRelation(User $user)
    {
        return $user->type === UserType::B2B_SELLER
            ? $user->B2bWithdrawalMethod()
            : $user->paymentMethods();
    }

    private function processPaystackRecipient($request, $method)
    {
        $bank = Bank::where('name', $request->bank_name)->first();
        if (!$bank) {
            return $this->error(null, "Selected bank not found!", 404);
        }

        PaystackService::createRecipient([
            'type' => "nuban",
            'name' => $request->account_name,
            'account_number' => $request->account_number,
            'bank_code' => $bank->code,
            'currency' => $bank->currency,
        ], $method);
    }
}
