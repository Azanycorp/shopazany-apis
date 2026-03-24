<?php

namespace App\DTO;

use App\Enum\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLog
{
    public function __construct(
        public User $user,
        public AuditEvent $event,
        public string $description,
        public Model|array|null $before = null,
        public ?Model $model = null,
        public ?string $tags = null
    ) {}
}
