<?php

namespace App\Enum;

enum OrderStatus: string
{
    const CONFIRMED = 'confirmed';

    const CANCELLED = 'cancelled';

    const DELIVERED = 'delivered';

    const COMPLETED = 'completed';

    const PENDING = 'pending';

    const PROCESSING = 'processing';

    const INPROGRESS = 'in-progress';

    const REVIEW = 'review';

    const SHIPPED = 'shipped';

    const PAID = 'paid';
    const READY_FOR_PICKUP = 'ready_for_pickup';
    const IN_TRANSIT = 'in_transit';
}
