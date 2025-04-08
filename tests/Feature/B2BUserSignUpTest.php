<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enum\UserType;
use App\Models\Action;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class B2BUserSignUpTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    public function setUp(): void
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
        $email = 'test'. rand(00, 99) . '@gmail.com';

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $email,
            'type' => UserType::B2B_SELLER,
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => true,
        ];

        $response = $this->postJson('/b2b/connect/seller/signup', $payload, $headers);

        $response->assertStatus(200)
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

        $response = $this->postJson('/b2b/connect/seller/signup', $payload, $headers);

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
            'referrer_code' => 'REF1234'
        ]);

        $password = $password = $this->faker->password(16);
        $email = 'test'. rand(00, 99) . '@gmail.com';

        $payload = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $email,
            'type' =>  UserType::B2B_SELLER,
            'password' => $password,
            'password_confirmation' => $password,
            'terms' => true,
        ];

        $response = $this->postJson('/b2b/connect/seller/signup?referrer=REF1234', $payload, $headers);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Created successfully']);

        $this->assertDatabaseHas('users', [
            'email' => $payload['email'],
        ]);
    }
}
