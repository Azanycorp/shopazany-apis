<?php

namespace App\Enum;

enum PaymentType: string
{
    const USERORDER = 'user_order';
    const RECURRINGCHARGE = 'recurring_charge';
}
