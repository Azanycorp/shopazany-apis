<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    protected $fillable = [
        'method',
        'url',
        'route',
        'headers',
        'payload',
        'response',
        'status_code',
        'ip_address',
        'user_agent',
        'user_id',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'payload' => 'array',
            'response' => 'array',
        ];
    }
}
