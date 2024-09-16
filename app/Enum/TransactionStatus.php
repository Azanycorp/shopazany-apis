<?php

namespace App\Enum;

enum TransactionStatus: string
{
    const PENDING = 'pending';
    const SUCCESSFUL = 'successful';
    const REJECTED = 'rejected';
}
