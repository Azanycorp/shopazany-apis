<?php

namespace App\Enum;

enum RfqStatus: string
{
    const CONFIRMED = 'confirmed';

    const PENDING = 'pending';

    const SHIPPED = 'shipped';

    const COMPLETED = 'completed';

    const DELIVERED = 'delivered';

    const IN_PROGRESS = 'in-progress';
}
