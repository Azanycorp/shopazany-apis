<?php

namespace App\Pipelines\Signup\B2B;

use App\Enum\UserLog;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Symfony\Component\HttpFoundation\Response;

class CreateB2BSeller
{
    use HttpResponse, SignUp;

    public function handle($request)
    {

        $user = $this->createB2BSeller($request);

        $description = "User with email: {$request->email} signed up";
        $response = $this->success(null, 'Created successfully', Response::HTTP_CREATED);
        $action = UserLog::CREATED;
        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }
}
