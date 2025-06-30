<?php

namespace App\Enum;

enum Coupon: string
{
    // Type
    const ONE_TIME = 'one-time';

    const MULTI_USE = 'multi-use';

    // Status
    const ACTIVE = 'active';

    const INACTIVE = 'in-active';
}
