<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\SubscriptionType;
use App\Notifications\ResetPasswordNotification;
use App\Trait\ClearsResponseCache;
use App\Trait\UserRelationship;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property array|string|null $referrer_link
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $referrals
 * @property int $category_count
 * @property string $fullName
 * @property-read bool $is_subscribed
 * @property-read UserSubcription $subscription_plan
 * @property-read string $type
 * @property-read UserWallet|null $userWallet
 * @property-read Wallet|null $wallet
 * @property-read UserSubcription $subscription_history
 * @property-read string $subscription_status
 */
class User extends Authenticatable
{
    use ClearsResponseCache, HasApiTokens, HasFactory, Notifiable, SoftDeletes, UserRelationship;

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
        'public_id',
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
        'pending_referrer_code',
        'biometric_enabled',
        'biometric_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_affiliate_member' => 'boolean',
            'referrer_link' => 'array',
            'two_factor_enabled' => 'boolean',
            'biometric_enabled' => 'boolean',
            'service_type' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function ($model): void {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function sendPasswordResetNotification($token): void
    {
        $email = $this->email;

        $repository = resolve(Repository::class);
        $url = $repository->get('services.reset_password_url').'?token='.$token.'&email='.$email;

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
            return $this->userSubscriptions()
                ->with('subscriptionPlan')
                ->get();
        });
    }

    protected function subscriptionPlan(): Attribute
    {
        return Attribute::make(get: function () {
            if (! array_key_exists('subscription_plan', $this->attributes)) {
                $this->attributes['subscription_plan'] = $this->userSubscriptions()
                    ->where('status', SubscriptionType::ACTIVE)
                    ->first();
            }

            return $this->attributes['subscription_plan'];
        });
    }

    public function activeSubscription(): ?UserSubcription
    {
        return $this->userSubscriptions()
            ->where('status', SubscriptionType::ACTIVE)
            ->first();
    }

    protected function subscriptionStatus(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->userSubscriptions()->latest()->value('status') ?? 'No Subscription'
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(get: function (): string {
            return "{$this->first_name} {$this->last_name}";
        });
    }

    protected function buyerName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => "{$this->first_name} {$this->last_name}"
        );
    }

    #[\Illuminate\Database\Eloquent\Attributes\Scope]
    protected function filterReferrals($query, $searchQuery, $statusFilter)
    {
        if (filled($searchQuery)) {
            $query->where(function ($q) use ($searchQuery): void {
                $q->where('first_name', 'LIKE', "%$searchQuery%")
                    ->orWhere('last_name', 'LIKE', "%$searchQuery%")
                    ->orWhere('email', 'LIKE', "%$searchQuery%");
            });
        }

        if (filled($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        return $query;
    }
}
