<?php

namespace App\Enum;

enum TransactionStatus
{
    const PENDING = "pending";
    const SUCCESSFUL = "successful";
    const REJECTED = "rejected";
}
