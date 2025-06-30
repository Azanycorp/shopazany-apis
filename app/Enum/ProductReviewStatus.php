<?php

namespace App\Enum;

enum ProductReviewStatus: string
{
    const APPROVED = 'approved';

    const PENDING = 'pending';

    const REJECTED = 'rejected';
}
