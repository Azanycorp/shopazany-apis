<?php

namespace App\Enum;

enum UserStatus: string
{
    const ACTIVE = 'active';
    const BLOCKED = 'blocked';
    const DELETED = 'deleted';
    const PENDING = 'pending';
    const SUSPENDED = 'suspended';
}
