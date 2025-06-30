<?php

namespace App\Enum;

enum PaymentType: string
{
    const USERORDER = 'user_order';

    const B2BUSERORDER = 'b2b_user_order';

    const RECURRINGCHARGE = 'recurring_charge';

    // Payment Methods
    const OFFLINE = 'offline';

    const PAYSTACK = 'paystack';

    const B2B_PAYSTACK = 'b2b_paystack';

    const AUTHORIZE = 'authorize';
}
