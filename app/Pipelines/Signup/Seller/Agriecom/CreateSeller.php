<?php

namespace App\Pipelines\Signup\Seller\Agriecom;

use App\Enum\UserLog;
use App\Enum\UserType;
use App\Models\User;
use App\Trait\HttpResponse;
use App\Trait\SignUp;

class CreateSeller
{
    use HttpResponse, SignUp;

    public function __construct(
        private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher,
    ) {}

    public function handle($request)
    {
        $code = generateVerificationCode();

        $user = User::query()->create([
            'email' => $request->input('email'),
            'type' => UserType::AGRIECOM_SELLER,
            'email_verified_at' => null,
            'verification_code' => $code,
            'is_verified' => 0,
            'password' => $this->bcryptHasher->make($request->password),
        ]);

        $description = "User with email: {$request->email} signed up";
        $response = $this->success(null, 'Created successfully', 201);
        $action = UserLog::CREATED;

        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }
}
