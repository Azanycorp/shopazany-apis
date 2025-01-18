<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email',
        'user_type',
        'action',
        'description',
        'ip',
        'url',
        'device',
        'request',
        'response',
        'performed_at'
    ];

    protected function cast():array{
        return [
            'device' => 'object'
        ];
    }
}
