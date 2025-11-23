<?php

namespace App\Pipelines\Verify;

use App\Services\Auth\Auth;
use App\Services\Auth\RequestOptions;
use App\Trait\HttpResponse;
use Symfony\Component\HttpFoundation\Response;

class VerifyWithAuthService
{
    use HttpResponse;

    public function __construct(
        protected Auth $auth
    ) {}

    public function handle($request)
    {
        $options = new RequestOptions(
            headers: [
                config('services.auth_service.key') => config('services.auth_service.value'),
            ],
            data: [
                'email' => $request->string('email'),
            ]
        );

        $response = $this->auth->post(config('services.auth_service.url').'/verify-code', $options);

        if ($response->failed() && $response->status() === Response::HTTP_UNPROCESSABLE_ENTITY) {
            return $this->error(null, $response['message'], Response::HTTP_BAD_REQUEST);
        }

        if ($response->failed()) {
            return $this->error(null, $response['message'], Response::HTTP_BAD_REQUEST);
        }

        return $request->response;
    }
}
