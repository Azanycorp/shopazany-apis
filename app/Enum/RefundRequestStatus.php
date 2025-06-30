<?php

namespace App\Enum;

enum RefundRequestStatus: string
{
    const COMPLETED = 'completed';

    const PENDING = 'pending';

    const REJECTED = 'rejected';
}
