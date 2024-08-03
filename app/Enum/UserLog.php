<?php

namespace App\Enum;

enum UserLog
{
    const LOGIN_ATTEMPT = 'login attempt';
    const LOGGED_IN = 'logged in';
    const CREATED = 'created';
    const UPDATED = 'updated';
    const DELETED = 'deleted';
    const VIEWED = 'viewed';
    const CODE_RESENT = 'code resent';
    const PASSWORD_FORGOT = 'password forgot';
    const PASSWORD_RESET = 'password reset';
    const LOGOUT = 'logged out';
}
