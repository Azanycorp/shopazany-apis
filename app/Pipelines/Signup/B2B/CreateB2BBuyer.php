<?php

namespace App\Pipelines\Signup\B2B;

use App\Enum\UserLog;
use App\Models\User;
use App\Trait\HttpResponse;
use App\Trait\SignUp;
use Symfony\Component\HttpFoundation\Response;

class CreateB2BBuyer
{
    use HttpResponse, SignUp;

    public function handle($request)
    {
        $code = generateVerificationCode();
        $currencyCode = currencyCodeByCountryId($request->country_id);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => $request->type,
            'service_type' => $request->service_type,
            'average_spend' => $request->average_spend,
            'company_name' => $request->company_name,
            'company_size' => $request->company_size,
            'website' => $request->website,
            'country' => $request->country_id,
            'default_currency' => $currencyCode,
            'email_verified_at' => null,
            'verification_code' => $code,
            'is_verified' => 0,
            'password' => bcrypt($request->password),
        ]);

        $user->b2bCompany()->create([
            'service_type' => $request->service_type,
            'average_spend' => $request->average_spend,
            'business_name' => $request->company_name,
            'company_size' => $request->company_size,
            'website' => $request->website,
            'country_id' => $request->country_id,
        ]);

        $description = "User with email: {$request->email} signed up";
        $response = $this->success(null, 'Created successfully', Response::HTTP_CREATED);
        $action = UserLog::CREATED;
        logUserAction($request, $action, $description, $response, $user);

        return $response;
    }
}
