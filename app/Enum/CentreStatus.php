<?php

namespace App\Enum;

enum CentreStatus: string
{
    const ACTIVE = 'active';

    const INACTIVE = 'inactive';

    const PROCESSING = 'processing';

    const MAINTENANCE = 'maintenenance';

    const PENDING = 'pending';
}
