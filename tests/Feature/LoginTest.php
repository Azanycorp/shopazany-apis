<?php

use App\Models\Action;
use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    Http::fake([
        '*' => Http::response([
            'status' => true,
            'data' => [
                'id' => 1,
                'email' => 'test@example.com',
            ],
        ], 200),
    ]);

    config(['services.payment_service.api_secret' => 'test-secret']);
    config(['services.payment_service.api_key' => 'test-key']);
    config(['services.auth_service.url' => 'http://auth.test']);
    config(['services.auth_service.key' => 'test-key']);
    config(['services.auth_service.value' => 'test-value']);

    Action::factory()->create([
        'slug' => 'create_an_account',
        'points' => 10,
    ]);
});

test('successful login', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'status' => 'active',
        'is_admin_approve' => true,
        'two_factor_enabled' => false,
    ]);

    $response = (new LoginService)->AuthLogin(mockRequest([
        'email' => $user->email,
        'password' => 'password',
    ]));

    $responseData = json_decode($response->getContent(), true);

    expect($responseData)->toHaveKey('status');
    expect($responseData['status'])->toBeTrue();
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toEqual('Login successful.');
});

test('login with unverified account', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'email_verified_at' => null,
        'verification_code' => '123456',
    ]);

    $response = (new LoginService)->AuthLogin(mockRequest([
        'email' => $user->email,
        'password' => 'password',
    ]));

    $responseData = json_decode($response->getContent(), true);

    expect($responseData)->toHaveKey('status');
    expect($responseData['status'])->toBeFalse();
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toEqual('Account not verified or inactive');
});

test('login with two factor authentication', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
        'email_verified_at' => now(),
        'status' => 'active',
        'is_admin_approve' => true,
        'two_factor_enabled' => true,
    ]);

    $response = (new LoginService)->AuthLogin(mockRequest([
        'email' => $user->email,
        'password' => 'password',
    ]));

    // Mail::assertSent(LoginVerifyMail::class);
    $responseData = json_decode($response->getContent(), true);

    expect($responseData)->toHaveKey('status');
    expect($responseData['status'])->toBeTrue();
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toEqual('Code has been sent to your email address.');
});

test('invalid credentials', function () {
    // validation rejects invalid emails before reaching auth attempt

    $response = (new LoginService)->AuthLogin(mockRequest([
        'email' => 'invalid@example.com',
        'password' => 'wrongpassword',
    ]));

    $responseData = json_decode($response->getContent(), true);

    expect($responseData)->toHaveKey('status');
    expect($responseData['status'])->toBeFalse();
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toEqual('Invalid credentials.');
});

function mockRequest(array $data)
{
    $request = Mockery::mock('Illuminate\Http\Request');

    $request->shouldReceive('only')
        ->andReturn($data);

    $request->shouldReceive('validated')
        ->andReturn($data);

    foreach ($data as $key => $value) {
        $request->shouldReceive('__get')->with($key)->andReturn($value);
        $request->shouldReceive('input')->with($key)->andReturn($value);
    }
    $request->shouldReceive('input')->andReturn($data);

    $request->shouldReceive('ip')
        ->andReturn('127.0.0.1');

    $request->shouldReceive('fullUrl')
        ->andReturn('http://localhost/test-url');

    $request->shouldReceive('getContent')
        ->andReturn(json_encode($data));

    $request->email = $data['email'];
    $request->password = $data['password'];

    return $request;
}
