<?php

namespace App\Enum;

enum RefundRequestType: string
{
    const CONFORMITY_OF_GOOD = 'conformity of good';

    const DAMAGED_GOODS = 'damaged goods';

    const WRONG_DELIVERY = 'wrong delivery';
}
