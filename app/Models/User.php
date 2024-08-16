<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\ResetPasswordNotification;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid', 'first_name', 'last_name', 'email', 'password', 'address', 'city', 'postal_code', 'phone', 'country', 'provider_id', 'email_verified_at', 'verification_code', 'login_code', 'login_code_expires_at', 'is_affiliate_member', 'referrer_code', 'referrer_link', 'date_of_birth', 'is_verified', 'income_type', 'image', 'is_affiliate_member', 'status', 'type', 'middlename', 'state_id', 'is_admin_approve',
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
        ];
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function($model) {
            $model->uuid = (string) Str::uuid();
        });

    }

    public function sendPasswordResetNotification($token): void
    {
        $email = $this->email;

        $url = config('services.reset_password_url').'?token='.$token.'&email='.$email;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function referrals()
    {
        return $this->belongsToMany(User::class, 'referral_relationships', 'referrer_id', 'referee_id');
    }

    public function referrer()
    {
        return $this->belongsToMany(User::class, 'referral_relationships', 'referrer_id', 'referee_id');
    }

    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class, 'user_id');
    }

    public function withdrawalRequest()
    {
        return $this->hasMany(WithdrawalRequest::class, 'user_id');
    }

    public function kyc()
    {
        return $this->hasOne(Kyc::class, 'user_id');
    }

    public function userbusinessinfo()
    {
        return $this->hasOne(UserBusinessInformation::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'user_id');
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

    public function userOrders(): HasMany
    {
        return $this->hasMany(User::class, 'user_id');
    }

    public function sellerOrders(): HasMany
    {
        return $this->hasMany(User::class, 'seller_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

}
