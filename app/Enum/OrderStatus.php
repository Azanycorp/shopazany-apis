<?php

namespace App\Enum;

enum OrderStatus: string
{
    const CONFIRMED = 'confirmed';
    const CANCELLED = 'cancelled';
    const DELIVERED = 'delivered';
    const PENDING = 'pending';
    const PROCESSING = 'processing';
    const SHIPPED = 'shipped';
}
