<?php

namespace App\Pipelines\BusinessInformation;

use App\Enum\UserLog;
use App\Models\User;
use App\Services\Auth\Auth;
use App\Services\Auth\RequestOptions;
use App\Trait\HttpResponse;
use Closure;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserAccount
{
    use HttpResponse;

    public function __construct(
        protected Auth $auth
    ) {}

    public function handle($request, Closure $next)
    {
        $user = User::with('businessInformation')->find($request->user_id);

        if (! $user) {
            return $this->error(null, 'User not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $options = new RequestOptions(
                headers: [
                    config('services.auth_service.key') => config('services.auth_service.value'),
                ],
                data: [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $user->email,
                    'country_id' => $request->country_id,
                    'phone' => $request->business_phone,
                ]
            );

            $response = $this->auth->patch(config('services.auth_service.url').'/update-account', $options);

            if ($response->failed()) {
                return $this->error(null, $response['message'], Response::HTTP_BAD_REQUEST);
            }

            $request->user = $user;

            return $next($request);
        } catch (\Exception $e) {
            $description = "Account update failed: {$request->email}";
            $response = $this->error(null, $e->getMessage(), Response::HTTP_BAD_REQUEST);
            $action = UserLog::FAILED;

            logUserAction($request, $action, $description, $response);

            return $response;
        }
    }
}
