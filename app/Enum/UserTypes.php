<?php

namespace App\Enum;

enum UserTypes: string
{
    case CUSTOMER = 'customer';
    case SELLER = 'seller';
    case AFFILIATE = 'affiliate';
    case B2B_SELLER = 'b2b_seller';
    case B2B_BUYER = 'b2b_buyer';
    case AGRIECOM_SELLER = 'agriecom_seller';
    case B2B_AGRIECOM_SELLER = 'b2b_agriecom_seller';
    case B2B_AGRIECOM_BUYER = 'b2b_agriecom_buyer';
}
