<?php

namespace Tests\Feature;

use App\Models\Action;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SignUpTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        Action::factory()->create([
            'slug' => 'create_account',
            'points' => 10,
        ]);
    }

    /**
     * Test successful user signup.
     */
    public function test_user_can_sign_up_successfully(): void
    {
        $headers = [
            config('security.header_key', 'X-SHPAZY-AUTH') => config('security.header_value'),
        ];

        $password = 'ValidPass123!@#';
        $email = 'test'.rand(00, 99).'@gmail.com';

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => true,
        ];

        $response = $this->postJson('/api/connect/signup', $payload, $headers);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Created successfully']);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'is_verified' => 0,
        ]);
    }

    /**
     * Test signup validation errors.
     */
    public function test_user_signup_validation_errors(): void
    {
        $headers = [
            config('security.header_key', 'X-SHPAZY-AUTH') => config('security.header_value'),
        ];

        $payload = [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'password' => 'pass',
            'password_confirmation' => 'different-pass',
            'terms' => null,
        ];

        $response = $this->postJson('/api/connect/signup', $payload, $headers);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email', 'password', 'terms']);
    }

    /**
     * Test referral code functionality.
     */
    public function test_user_signup_with_referral_code(): void
    {
        $headers = [
            config('security.header_key', 'X-SHPAZY-AUTH') => config('security.header_value'),
        ];

        User::factory()->create([
            'referrer_code' => 'REF1234',
        ]);

        $password = $password = $this->faker->password(16);
        $email = 'test'.rand(00, 99).'@gmail.com';

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => true,
        ];

        $response = $this->postJson('/api/connect/signup?referrer=REF1234', $payload, $headers);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Created successfully']);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
        ]);
    }
}
