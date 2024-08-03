<?php

namespace App\Http\Controllers;

use App\Actions\UserLogAction;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class Controller
{
    use HttpResponse;

    public function generateUniqueReferrerCode()
    {
        do {
            $referrer_code = Str::random(10);
        } while (User::where('referrer_code', $referrer_code)->exists());

        return $referrer_code;
    }

    public function generateAlternateReferrerCode()
    {
        return strrev(Str::random(6) . rand(4, 9876));
    }

    public function getUserReferrer($user)
    {
        if($user->referrer_code !== null){
            return $this->error(null, 'Account has been created', 400);
        }
    }

    protected function userAuth()
    {
        return Auth::user();
    }

    public function logUserAction($request, $action, $description, $response, $user = null)
    {
        (new UserLogAction($request, $action, $description, $response, $user))->run();
    }
}
