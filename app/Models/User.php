<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enum\SubscriptionType;
use App\Notifications\ResetPasswordNotification;
use App\Trait\ClearsResponseCache;
use App\Trait\UserRelationship;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property string|null $uuid
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $middlename
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $type
 * @property string|null $address
 * @property string|null $city
 * @property string|null $postal_code
 * @property string|null $phone
 * @property string|null $country
 * @property string|null $state_id
 * @property string|null $provider_id
 * @property string|null $verification_code
 * @property string|null $referrer_code
 * @property array<array-key, mixed>|null $referrer_link
 * @property string|null $date_of_birth
 * @property int|null $is_verified
 * @property string $income_type
 * @property string|null $image
 * @property string|null $public_id
 * @property string $default_currency
 * @property bool $is_affiliate_member
 * @property string|null $login_code
 * @property string|null $login_code_expires_at
 * @property string $status
 * @property bool $is_admin_approve
 * @property string|null $remember_token
 * @property bool|null $two_factor_enabled
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property array<array-key, mixed>|null $service_type
 * @property string|null $average_spend
 * @property string|null $company_name
 * @property string|null $company_size
 * @property string|null $website
 * @property string|null $info_source
 * @property string|null $pending_referrer_code
 * @property string|null $bio
 * @property string|null $gender
 * @property bool $biometric_enabled
 * @property string|null $biometric_token
 * @property string|null $hear_about_us
 * @property string|null $last_user_type
 * @property string|null $date_switched
 * @property string|null $fcm_token
 * @property string|null $code
 * @property-read string $fullName
 * @property-read int|null $category_count
 * @property Carbon|null $expires_at
 * @property-read B2bCompany|null $b2bCompany
 * @property-read Collection<int, B2bOrderRating> $b2bOrderRate
 * @property-read int|null $b2b_order_rate_count
 * @property-read Collection<int, B2BProduct> $b2bProducts
 * @property-read int|null $b2b_products_count
 * @property-read Collection<int, B2BSellerShippingAddress> $b2bSellerShippingAddresses
 * @property-read int|null $b2b_seller_shipping_addresses_count
 * @property-read Collection<int, B2bWithdrawalMethod> $b2bWithdrawalMethod
 * @property-read int|null $b2b_withdrawal_method_count
 * @property-read BankAccount|null $bankAccount
 * @property-read BusinessInformation|null $businessInformation
 * @property-read string $buyer_name
 * @property-read Collection<int, Promo> $coupons
 * @property-read int|null $coupons_count
 * @property-read string $full_name
 * @property-read mixed $is_subscribed
 * @property-read Kyc|null $kyc
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, OrderRate> $orderRate
 * @property-read int|null $order_rate_count
 * @property-read Collection<int, PaymentMethod> $paymentMethods
 * @property-read int|null $payment_methods_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, ProductAttribute> $productAttributes
 * @property-read int|null $product_attributes_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, PromoRedemption> $promoRedemptions
 * @property-read int|null $promo_redemptions_count
 * @property-read Collection<int, RedeemPoint> $reedemPoints
 * @property-read int|null $reedem_points_count
 * @property-read Collection<int, User> $referrals
 * @property-read int|null $referrals_count
 * @property-read Collection<int, User> $referrer
 * @property-read int|null $referrer_count
 * @property-read Collection<int, Order> $sellerOrders
 * @property-read int|null $seller_orders_count
 * @property-read State|null $state
 * @property-read mixed $subscription_history
 * @property-read mixed $subscription_plan
 * @property-read mixed $subscription_status
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read Collection<int, Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read Collection<int, UserAction> $userActions
 * @property-read int|null $user_actions_count
 * @property-read Collection<int, UserActivityLog> $userActivityLog
 * @property-read int|null $user_activity_log_count
 * @property-read Country|null $userCountry
 * @property-read Collection<int, Order> $userOrders
 * @property-read int|null $user_orders_count
 * @property-read Collection<int, UserShippingAddress> $userShippingAddress
 * @property-read int|null $user_shipping_address_count
 * @property-read Collection<int, UserSubcription> $userSubscriptions
 * @property-read int|null $user_subscriptions_count
 * @property-read UserWallet|null $userWallet
 * @property-read UserBusinessInformation|null $userbusinessinfo
 * @property-read Wallet|null $wallet
 * @property-read Collection<int, Wishlist> $wishlist
 * @property-read int|null $wishlist_count
 * @property-read Collection<int, WithdrawalRequest> $withdrawalRequests
 * @property-read int|null $withdrawal_requests_count
 *
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User filterReferrals($searchQuery, $statusFilter)
 * @method static Builder<static>|User isNotAffiliateMember()
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User onlyTrashed()
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereAddress($value)
 * @method static Builder<static>|User whereAverageSpend($value)
 * @method static Builder<static>|User whereBio($value)
 * @method static Builder<static>|User whereBiometricEnabled($value)
 * @method static Builder<static>|User whereBiometricToken($value)
 * @method static Builder<static>|User whereCity($value)
 * @method static Builder<static>|User whereCode($value)
 * @method static Builder<static>|User whereCompanyName($value)
 * @method static Builder<static>|User whereCompanySize($value)
 * @method static Builder<static>|User whereCountry($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereDateOfBirth($value)
 * @method static Builder<static>|User whereDateSwitched($value)
 * @method static Builder<static>|User whereDefaultCurrency($value)
 * @method static Builder<static>|User whereDeletedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereExpiresAt($value)
 * @method static Builder<static>|User whereFcmToken($value)
 * @method static Builder<static>|User whereFirstName($value)
 * @method static Builder<static>|User whereGender($value)
 * @method static Builder<static>|User whereHearAboutUs($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereImage($value)
 * @method static Builder<static>|User whereIncomeType($value)
 * @method static Builder<static>|User whereInfoSource($value)
 * @method static Builder<static>|User whereIsAdminApprove($value)
 * @method static Builder<static>|User whereIsAffiliateMember($value)
 * @method static Builder<static>|User whereIsVerified($value)
 * @method static Builder<static>|User whereLastName($value)
 * @method static Builder<static>|User whereLastUserType($value)
 * @method static Builder<static>|User whereLoginCode($value)
 * @method static Builder<static>|User whereLoginCodeExpiresAt($value)
 * @method static Builder<static>|User whereMiddlename($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User wherePendingReferrerCode($value)
 * @method static Builder<static>|User wherePhone($value)
 * @method static Builder<static>|User wherePostalCode($value)
 * @method static Builder<static>|User whereProviderId($value)
 * @method static Builder<static>|User wherePublicId($value)
 * @method static Builder<static>|User whereReferrerCode($value)
 * @method static Builder<static>|User whereReferrerLink($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereServiceType($value)
 * @method static Builder<static>|User whereStateId($value)
 * @method static Builder<static>|User whereStatus($value)
 * @method static Builder<static>|User whereTwoFactorEnabled($value)
 * @method static Builder<static>|User whereType($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @method static Builder<static>|User whereUuid($value)
 * @method static Builder<static>|User whereVerificationCode($value)
 * @method static Builder<static>|User whereWebsite($value)
 * @method static Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|User withoutTrashed()
 *
 * @mixin \Eloquent
 */
#[Fillable([
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
    'status',
    'type',
    'last_user_type',
    'date_switched',
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
    'hear_about_us',
    'fcm_token',
    'code',
    'expires_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    use ClearsResponseCache, HasApiTokens, HasFactory, Notifiable, SoftDeletes, UserRelationship;

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
            'expires_at' => 'datetime',
            'is_admin_approve' => 'boolean',
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

    protected function hearAboutUs(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => $value ? strtolower($value) : null
        );
    }

    #[Scope]
    protected function filterReferrals($query, $searchQuery, $statusFilter)
    {
        if (filled($searchQuery)) {
            $query->where(function ($q) use ($searchQuery): void {
                $q->whereAny(
                    ['first_name', 'last_name', 'middlename', 'email'],
                    'LIKE',
                    "%$searchQuery%"
                );
            });
        }

        if (filled($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        return $query;
    }

    #[Scope]
    protected function isNotAffiliateMember(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('is_affiliate_member', false)
                ->orWhereNull('is_affiliate_member');
        });
    }

    public function hasReachedProductLimit(): bool
    {
        $subscription = $this->activeSubscription();

        if (! $subscription) {
            return false;
        }

        $plan = $subscription->subscriptionPlan;

        // No plan or unlimited
        if (! $plan || $plan->product_limit === null) {
            return false;
        }

        return $this->products()->withoutGlobalScopes()->count() >= $plan->product_limit;
    }
}
