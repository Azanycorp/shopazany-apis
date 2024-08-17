<?php

namespace App\Services;

use App\Models\Transaction;

class TransactionService
{
    protected $user;
    protected $type;
    protected $amount;

    public function __construct($user, $type, $amount)
    {
        $this->user = $user;
        $this->type = $type;
        $this->amount = $amount;
    }

    public function logTransaction()
    {
        try {

            Transaction::create([
                'user_id' => $this->user->id,
                'reference' => generateTransactionReference(),
                'type' => $this->type,
                'date' => now(),
                'amount' => $this->amount,
                'status' => 'pending'
            ]);

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}

