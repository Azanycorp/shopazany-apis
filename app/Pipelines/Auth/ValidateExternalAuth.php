<?php

namespace App\Pipelines\Auth;

use App\Services\Auth\LoginService;
use App\Trait\HttpResponse;
use App\Trait\Login;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class ValidateExternalAuth
{
    use HttpResponse, Login;

    public function handle($request, Closure $next)
    {
        $response = LoginService::externalAuthCheck($request);

        if ($response->failed()) {
            return $this->error(null, $response['message'], Response::HTTP_UNAUTHORIZED);
        }

        if ($response->status() === 422) {
            return $this->error(null, $response['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $externalUser = $response->successful() ? $response->json() : false;

        if (! $externalUser) {
            return $this->handleInvalidCredentials($request);
        }

        $request->externalUser = $externalUser;

        return $next($request);
    }
}
