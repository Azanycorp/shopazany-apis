<?php

namespace App\Enum;

enum RfqStatus: string
{
    const CONFIRMED = 'confirmed';
    const PENDING = 'pending';
    const DELIVERED = 'delivered';
    const IN_PROGRESS = 'in-progress';
}
