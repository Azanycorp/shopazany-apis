<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'cost',
        'country_id',
        'period',
        'designation',
        'tagline',
        'details',
        'status',
        'tier',
        'currency',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'tagline' => 'array',
        ];
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubcription::class, 'subscription_plan_id');
    }
}
