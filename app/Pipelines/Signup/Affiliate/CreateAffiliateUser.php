<?php

namespace App\Pipelines\Signup\Affiliate;

use App\Enum\MailingEnum;
use App\Enum\UserLog;
use App\Enum\UserType;
use App\Mail\SignUpVerifyMail;
use App\Models\Action;
use App\Models\User;
use App\Trait\HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CreateAffiliateUser
{
    use HttpResponse;

    public function __construct(
        private readonly \Illuminate\Hashing\BcryptHasher $bcryptHasher,
    ) {}

    public function handle($request)
    {
        $user = null;

        try {
            $user = User::where('email', $request->email)->first();
            $response = $this->handleExistingUser($user);

            if ($response) {
                return $response;
            }

            if ($request->referrer_code) {
                $referrer = User::where('referrer_code', $request->referrer_code)->first();

                if ($referrer && (! $referrer->email_verified_at || $referrer->is_verified != 1)) {
                    $description = "User with referral code and email {$referrer->email} has not been verified";
                    $action = UserLog::CREATED;
                    $response = $this->error(null, 'User with referral code has not been verified', 400);

                    logUserAction($request, $action, $description, $response, $user);

                    return $response;
                }
            }

            DB::transaction(function () use ($request, $user): void {
                $referrer_code = $this->determineReferrerCode($request);

                $referrer_links = generate_referrer_links($referrer_code);
                $code = generateVerificationCode();

                $data = $this->userTrigger($user, $request, $referrer_links, $referrer_code, $code);

                if ($request->referrer_code) {
                    $this->handleReferrer($request->referrer_code, $data);
                }
            });

            return $this->success(null, 'Created successfully', Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $description = 'User creation failed';
            $action = UserLog::FAILED;
            $response = $this->error(null, $e->getMessage(), 500);

            logUserAction($request, $action, $description, $response, $user);

            return $response;
        }
    }

    private function determineReferrerCode($request)
    {
        $initial_referrer_code = Str::random(10);
        if (! $request->referrer_code) {
            return $initial_referrer_code;
        }
        if (User::where('referrer_code', $request->referrer_code)->exists()) {
            return $this->generateUniqueReferrerCode();
        }

        return $request->referrer_code;
    }

    private function handleExistingUser($user)
    {
        if ($user) {
            return $this->getUserReferrer($user);
        }

        return null;
    }

    private function handleReferrer($referrer_code, $data): void
    {
        $referrer = User::with(['wallet', 'referrer'])
            ->where('referrer_code', $referrer_code)
            ->first();

        if (! $referrer || ! $referrer->is_affiliate_member) {
            throw new \Exception('You are not a valid referrer');
        }

        $points = Action::where('slug', 'create_account')->first()->points ?? 0;
        $referrer->wallet()->increment('reward_point', $points);
        $referrer->referrer()->attach($data);
        $referrer->save();
    }

    private function userTrigger($user, $request, array $referrer_links, $referrer_code, string $code)
    {
        $currencyCode = currencyCodeByCountryId($request->country_id);

        if ($user) {
            $emailVerified = $user->email_verified_at;

            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'country' => $request->country_id,
                'state_id' => $request->state_id,
                'default_currency' => $currencyCode,
                'type' => UserType::SELLER,
                'referrer_code' => $referrer_code,
                'referrer_link' => $referrer_links,
                'is_verified' => 1,
                'is_affiliate_member' => 1,
                'password' => $this->bcryptHasher->make($request->password),
            ]);

            $description = "User with email {$request->email} signed up as an affiliate";
            $action = UserLog::CREATED;
            $response = $this->success(null, 'Created successfully');

            logUserAction($request, $action, $description, $response, $user);

            if (is_null($emailVerified)) {
                $user->update(['email_verified_at' => null, 'verification_code' => $code]);

                $type = MailingEnum::SIGN_UP_OTP;
                $subject = 'Verify Account';
                $mail_class = SignUpVerifyMail::class;
                $data = [
                    'user' => $user,
                ];
                mailSend($type, $user, $subject, $mail_class, $data);
            }

            return $user;
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'type' => UserType::SELLER,
            'default_currency' => $currencyCode,
            'email_verified_at' => null,
            'verification_code' => $code,
            'country' => $request->country_id,
            'state_id' => $request->state_id,
            'is_verified' => 0,
            'is_affiliate_member' => 1,
            'password' => $this->bcryptHasher->make($request->password),
        ]);

        $description = "User with email {$request->email} signed up as an affiliate";
        $action = UserLog::CREATED;
        $response = $this->success(null, 'Created successfully', 201);
        logUserAction($request, $action, $description, $response, $user);

        return $user;
    }

    private function getUserReferrer($user)
    {
        if ($user->referrer_code !== null) {
            return $this->error(null, 'Account has been created', 403);
        }

        return null;
    }

    private function generateUniqueReferrerCode()
    {
        do {
            $referrer_code = Str::random(10);
        } while (User::where('referrer_code', $referrer_code)->exists());

        return $referrer_code;
    }
}
