<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Support\Str;
use App\Enum\SubscriptionType;
use App\Trait\UserRelationship;
use Laravel\Sanctum\HasApiTokens;
use App\Trait\ClearsResponseCache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, ClearsResponseCache, UserRelationship;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'city',
        'postal_code',
        'phone',
        'country',
        'provider_id',
        'email_verified_at',
        'verification_code',
        'login_code',
        'login_code_expires_at',
        'is_affiliate_member',
        'referrer_code',
        'info_source',
        'referrer_link',
        'date_of_birth',
        'is_verified',
        'income_type',
        'image',
        'is_affiliate_member',
        'status',
        'type',
        'middlename',
        'state_id',
        'is_admin_approve',
        'two_factor_enabled',
        'default_currency',
        'service_type',
        'average_spend',
        'company_name',
        'company_size',
        'website',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_affiliate_member' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function($model): void {
            $model->uuid = (string) Str::uuid();
        });

    }

    public function sendPasswordResetNotification($token): void
    {
        $email = $this->email;

        $url = config('services.reset_password_url').'?token='.$token.'&email='.$email;

        $this->notify(new ResetPasswordNotification($url));
    }

    public static function getUserEmail($email)
    {
        return self::where('email', $email)->first();
    }

    public static function getUserID($id)
    {
        return self::with(['userbusinessinfo', 'products', 'userOrders', 'sellerOrders'])
        ->where('id', $id)
        ->first();
    }

    protected function isSubscribed(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->userSubscriptions()
                ->where('status', SubscriptionType::ACTIVE)
                ->exists();
        });
    }

    protected function subscriptionHistory(): Attribute
    {
        return Attribute::make(get: function () {
            return $this->userSubscriptions()->where('status', SubscriptionType::ACTIVE)->get();
        });
    }

    protected function subscriptionPlan(): Attribute
    {
        return Attribute::make(get: function () {
            if (!array_key_exists('subscription_plan', $this->attributes)) {
                $this->attributes['subscription_plan'] = $this->userSubscriptions()
                    ->where('status', SubscriptionType::ACTIVE)
                    ->first();
            }
            return $this->attributes['subscription_plan'];
        });
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(get: function (): string {
            return "{$this->first_name} {$this->last_name}";
        });
    }

}
