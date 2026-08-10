<?php

use App\Models\Action;
use App\Models\User;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);
uses(WithFaker::class);

beforeEach(function () {
    $this->repository = resolve(Repository::class);

    Http::fake([
        '*' => Http::response([
            'status' => true,
            'message' => 'Created successfully',
            'data' => [
                'id' => 1,
                'email' => 'test@example.com',
            ],
        ], 200),
    ]);

    DB::table('countries')->insertOrIgnore([
        'id' => 160,
        'name' => 'Nigeria',
    ]);
    DB::table('states')->insertOrIgnore([
        'id' => 1,
        'country_id' => 160,
        'name' => 'Lagos',
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

test('user can sign up successfully', function () {
    $headers = [
        $this->repository->get('security.header_key', 'X-SHPAZY-AUTH') => $this->repository->get('security.header_value'),
    ];

    $password = 'ValidPass123!@#';
    $email = 'test'.rand(00, 99).'@gmail.com';

    $payload = [
        'first_name' => $this->faker->firstName(),
        'last_name' => $this->faker->lastName(),
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'country_id' => 160,
        'state_id' => 1,
        'terms' => true,
    ];

    $response = $this->postJson('/api/connect/signup', $payload, $headers);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Created successfully']);

    $this->assertDatabaseHas('users', [
        'email' => $payload['email'],
        'first_name' => $payload['first_name'],
        'last_name' => $payload['last_name'],
        'country' => $payload['country_id'],
        'state_id' => $payload['state_id'],
        'is_verified' => 0,
    ]);
});

test('user signup validation errors', function () {
    $headers = [
        $this->repository->get('security.header_key', 'X-SHPAZY-AUTH') => $this->repository->get('security.header_value'),
    ];

    $payload = [
        'first_name' => '',
        'last_name' => '',
        'email' => 'invalid-email',
        'password' => 'pass',
        'password_confirmation' => 'different-pass',
        'country_id' => 0,
        'state_id' => 1,
        'terms' => null,
    ];

    $response = $this->postJson('/api/connect/signup', $payload, $headers);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password', 'terms']);
});

test('user signup with referral code', function () {
    $headers = [
        $this->repository->get('security.header_key', 'X-SHPAZY-AUTH') => $this->repository->get('security.header_value'),
    ];

    User::factory()->create([
        'referrer_code' => 'REF1234',
    ]);

    $password = 'ValidPass123!@#';
    $email = 'test'.rand(00, 99).'@gmail.com';

    $payload = [
        'first_name' => $this->faker->firstName(),
        'last_name' => $this->faker->lastName(),
        'email' => $email,
        'password' => $password,
        'password_confirmation' => $password,
        'country_id' => 160,
        'state_id' => 1,
        'terms' => true,
    ];

    $response = $this->postJson('/api/connect/signup?referrer=REF1234', $payload, $headers);

    $response->assertStatus(201)
        ->assertJson(['message' => 'Created successfully']);

    $this->assertDatabaseHas('users', [
        'email' => $payload['email'],
        'country' => $payload['country_id'],
    ]);
});
