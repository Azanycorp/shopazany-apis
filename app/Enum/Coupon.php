<?php

namespace App\Enum;

enum Coupon: string
{
    // Type
    case ONE_TIME = 'one-time';
    case MULTI_USE = 'multi-use';
    // Status
    case ACTIVE = 'active';
    case INACTIVE = 'in-active';
}
