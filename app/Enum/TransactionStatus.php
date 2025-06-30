<?php

namespace App\Enum;

enum TransactionStatus: string
{
    const PENDING = 'pending';

    const SUCCESSFUL = 'successful';

    const REJECTED = 'rejected';

    // Type
    const WITHDRAWAL = 'withdrawal';

    const TRANSFER = 'transfer';
}
