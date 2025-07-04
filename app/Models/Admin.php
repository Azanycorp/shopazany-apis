<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
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
    ];

    protected $hidden = [
        'password',
    ];

    public function sendPasswordResetNotification($token): void
    {
        $email = $this->email;

        $url = config('services.reset_password_url') . '?token=' . $token . '&email=' . $email;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'admin_id');
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'admin_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
