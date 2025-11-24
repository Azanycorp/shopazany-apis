<?php

namespace App\Pipelines\Auth;

use App\Models\User;
use App\Services\Auth\LoginService;
use App\Trait\HttpResponse;
use App\Trait\Login;
use Closure;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ValidateExternalAuth
{
    use HttpResponse, Login;

    public function handle($request, Closure $next)
    {
        try {
            $response = LoginService::externalAuthCheck($request);
            $localUser = User::where('email', $request->email)->first();

            if ($response->status() === 422) {
                return $this->error(null, $response['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($localUser && ! Hash::check($request->input('password'), $localUser->password)) {
                return $this->handleInvalidCredentials($request);
            }

            /**
             * External auth succeeded (meaning user exists there)
             * → Proceed normally
             */
            if ($response->ok()) {
                $externalUser = $response->successful() ? $response->json() : false;

                if (! $externalUser) {
                    return $this->handleInvalidCredentials($request);
                }

                $request->externalUser = $response['data'];
                $request->user = $localUser;

                return $next($request);
            }

            /**
             * External auth FAILED (user does NOT exist in external service)
             * But user exists LOCALLY → Sync local → external
             */
            if ($localUser && $response->failed()) {
                $created = LoginService::syncLocalUserToAuthService($localUser, $request->password);

                if ($created->failed()) {
                    return $this->error(null, $created['message'], Response::HTTP_BAD_REQUEST);
                }

                $request->externalUser = $created['data'];
                $request->user = $localUser;

                return $next($request);
            }

            return $this->error(null, "Account doesn't exist!", Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $th) {
            return $this->error(null, "Something went wrong!: {$th->getMessage()}", Response::HTTP_BAD_REQUEST);
        }
    }
}
