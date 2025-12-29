<?php

namespace App\Pipelines\Signup\B2B;

use App\Enum\UserLog;
use App\Services\Auth\Auth;
use App\Services\Auth\RequestOptions;
use App\Trait\HttpResponse;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CreateB2BWithAuthService
{
    use HttpResponse;

    public function __construct(
        protected Auth $auth
    ) {}

    public function handle($request, Closure $next)
    {
        $names = extractNamesFromEmail($request->email);
        try {
            $options = new RequestOptions(
                headers: [
                    config('services.auth_service.key') => config('services.auth_service.value'),
                ],
                data: [
                    'first_name' => $request->first_name ?? $names['first_name'],
                    'last_name' => $request->last_name ?? $names['last_name'],
                    'email' => $request->email,
                    'type' => $request->type,
                    'country_id' => $request->country_id ?? 160,
                    'state_id' => $request->state_id ?? 24,
                    'password' => bcrypt($request->password),
                    'signed_up_from' => 'azany_b2b',
                ]
            );

            $response = $this->auth->post(config('services.auth_service.url').'/register', $options);

            if ($response->failed() && $response->status() === Response::HTTP_UNPROCESSABLE_ENTITY) {
                return $this->error(null, $response['message'], Response::HTTP_BAD_REQUEST);
            }

            if ($response->failed()) {
                return $this->error(null, $response['message'], Response::HTTP_BAD_REQUEST);
            }

            return $next($request);
        } catch (\Exception $e) {
            $description = "Sign up failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), Response::HTTP_BAD_REQUEST);
            $action = UserLog::FAILED;

            logUserAction($request, $action, $description, $response);

            return $response;
        }
    }
}
