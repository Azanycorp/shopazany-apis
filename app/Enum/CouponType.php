<?php

namespace App\Enum;

enum CouponType: string
{
    const PRODUCT = 'product';
    const TOTAL_ORDERS = 'total_orders';
    const WELCOME_COUPON = 'welcome_coupon';
}
