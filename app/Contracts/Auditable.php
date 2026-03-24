<?php

namespace App\Contracts;

interface Auditable
{
    public function prepareAuditValues(array $values): array;

    /** @return list<string> */
    public function auditTags(): array;

    public function shouldAudit(): bool;
}
