<?php

namespace App\Services;

use App\Enum\TransactionStatus;
use App\Models\Transaction;

class TransactionService
{
    protected $user;

    protected $type;

    protected $amount;

    protected $status;

    public function __construct($user, $type, $amount, $status = TransactionStatus::PENDING)
    {
        $this->user = $user;
        $this->type = $type;
        $this->amount = $amount;
        $this->status = $status;
    }

    public function logTransaction(): void
    {
        Transaction::create([
            'user_id' => $this->user->id,
            'reference' => generateTransactionReference(),
            'type' => $this->type,
            'date' => now(),
            'amount' => $this->amount,
            'status' => $this->status,
        ]);
    }
}
