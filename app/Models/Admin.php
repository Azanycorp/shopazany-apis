<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @property int $id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string $email
 * @property string|null $verification_code
 * @property string|null $verification_code_expire_at
 * @property string|null $phone_number
 * @property string $password
 * @property string $type Handeles admin user type eg b2c,b2b for b2b_admin admin user and b2c_admin admin user
 * @property int $two_factor_enabled
 * @property array<array-key, mixed>|null $modules
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $fullName
 * @property-read Collection<int, Blog> $blogs
 * @property-read int|null $blogs_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereModules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereVerificationCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Admin whereVerificationCodeExpireAt($value)
 *
 * @mixin \Eloquent
 */
#[Fillable([
    'first_name',
    'last_name',
    'email',
    'type',
    'phone_number',
    'password',
    'two_factor_enabled',
    'status',
    'verification_code',
    'verification_code_expire_at',
    'modules',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected function fullName(): Attribute
    {
        return Attribute::make(get: function (): string {
            return "{$this->first_name} {$this->last_name}";
        });
    }

    protected function casts(): array
    {
        return [
            'modules' => 'array',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $email = $this->email;

        $url = config('services.reset_password_url').'?token='.$token.'&email='.$email;

        $this->notify(new ResetPasswordNotification($url));
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'admin_id');
    }

    /**
     * @return HasMany<Blog, $this>
     */
    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'admin_id');
    }

    /**
     * @return BelongsToMany<Role, $this, Pivot>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return BelongsToMany<Permission, $this, Pivot>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
