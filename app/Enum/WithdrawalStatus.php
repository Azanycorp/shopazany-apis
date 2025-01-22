<?php

namespace App\Enum;

enum WithdrawalStatus: string
{
    const ENABLED = 'enabled';
    const DISABLED = 'disabled';
    const ACTIVE = 'active';
}
