<?php

namespace App\Enum;

enum WithdrawalStatus: string
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
    const ACTIVE = 'active';
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const COMPLETED = 'completed';
    const FAILED = 'failed';
}
