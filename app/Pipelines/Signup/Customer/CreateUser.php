<?php

namespace App\Pipelines\Signup\Customer;

use App\Enum\UserLog;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Symfony\Component\HttpFoundation\Response;

class CreateUser
{
    use HttpResponse, SignUp;

    public function handle($request)
    {
        $user = $this->createUser($request);

        $description = "User with email: {$request->email} signed up";
        $response = $this->success(null, 'Created successfully', Response::HTTP_CREATED);
        $action = UserLog::CREATED;
        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }
}
