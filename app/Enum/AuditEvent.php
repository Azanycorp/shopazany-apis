<?php

namespace App\Enum;

enum AuditEvent: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case ForceDeleted = 'force_deleted';

    // Auth
    case LoggedIn = 'logged_in';
    case LoggedOut = 'logged_out';
    case LoginFailed = 'login_failed';
    case PasswordReset = 'password_reset';
    case PasswordChanged = 'password_changed';
    case TokenCreated = 'token_created';
    case TokenRevoked = 'token_revoked';

    // Data access
    case Viewed = 'viewed';
    case Exported = 'exported';
    case Imported = 'imported';
    case Downloaded = 'downloaded';

    // Custom — use this when none of the above fit
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Updated => 'Updated',
            self::Deleted => 'Deleted',
            self::Restored => 'Restored',
            self::ForceDeleted => 'Permanently Deleted',
            self::LoggedIn => 'Logged In',
            self::LoggedOut => 'Logged Out',
            self::LoginFailed => 'Login Failed',
            self::PasswordReset => 'Password Reset',
            self::PasswordChanged => 'Password Changed',
            self::TokenCreated => 'Token Created',
            self::TokenRevoked => 'Token Revoked',
            self::Viewed => 'Viewed',
            self::Exported => 'Exported',
            self::Imported => 'Imported',
            self::Downloaded => 'Downloaded',
            self::Custom => 'Custom Event',
        };
    }

    public function isCritical(): bool
    {
        return in_array($this, [
            self::Deleted,
            self::ForceDeleted,
            self::LoginFailed,
            self::PasswordReset,
            self::PasswordChanged,
            self::TokenRevoked,
        ]);
    }
}
