<?php

namespace App\Traits;

use App\Models\AdminNotification;

trait SuperAdminNotification
{
    public function createNotification($title, $content): void
    {
        AdminNotification::create([
            'title' => $title,
            'content' => $content,
        ]);
    }
}
