<?php

namespace App\Enum;

enum ProductStatus: string
{
    const ACTIVE = 'active';
    const PENDING = 'pending';
    const OUT_OF_STOCK = 'out-of-stock';
    const DELETED = 'deleted';
    const DECLINED = 'declined';
}
