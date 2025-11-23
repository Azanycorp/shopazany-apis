<?php

namespace App\Pipelines\Signup\Customer;

use App\Enum\UserLog;
use App\Services\Auth\Auth;
use App\Services\Auth\RequestOptions;
use App\Trait\HttpResponse;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class CreateWithAuthService
{
    use HttpResponse;

    public function __construct(
        protected Auth $auth
    ) {}

    public function handle($request, Closure $next)
    {
        try {
            $options = new RequestOptions(
                headers: [
                    config('services.auth_service.key') => config('services.auth_service.value'),
                ],
                data: [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'type' => $request->type,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'password' => bcrypt($request->password),
                    'signed_up_from' => 'azany_b2c',
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
