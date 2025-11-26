<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $name
 */
class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
