<?php

namespace App\Enum;

enum UserType: string
{
    const CUSTOMER = 'customer';
    const SELLER = 'seller';
    const B2B_SELLER = 'b2b_seller';
    const B2B_BUYER = 'b2b_buyer';
    const AGRIECOM_SELLER = 'agriecom_seller';
}
