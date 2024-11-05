<?php

namespace Tests\Unit;

use App\Mail\LoginVerifyMail;
use App\Models\Action;
use App\Models\User;
use App\Services\Auth\LoginService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        Action::factory()->create([
            'slug' => 'create_account',
            'points' => 10,
        ]);
    }

    public function test_successful_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'status' => 'active',
            'is_admin_approve' => true,
            'two_factor_enabled' => false,
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => $user->email, 'password' => 'password'])
            ->andReturn(true);

        $response = (new LoginService)->AuthLogin($this->mockRequest([
            'email' => $user->email,
            'password' => 'password',
        ]));

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Login successful.', $responseData['message']);
    }

    public function test_login_with_unverified_account()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'email_verified_at' => null,
            'verification_code' => '123456',
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => $user->email, 'password' => 'password'])
            ->andReturn(true);

        $response = (new LoginService)->AuthLogin($this->mockRequest([
            'email' => $user->email,
            'password' => 'password',
        ]));

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertFalse($responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Account not verified or inactive', $responseData['message']);
    }

    public function test_login_with_two_factor_authentication()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'status' => 'active',
            'is_admin_approve' => true,
            'two_factor_enabled' => true,
        ]);

        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => $user->email, 'password' => 'password'])
            ->andReturn(true);

        $response = (new LoginService)->AuthLogin($this->mockRequest([
            'email' => $user->email,
            'password' => 'password',
        ]));

        Mail::assertSent(LoginVerifyMail::class);

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertTrue($responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Code has been sent to your email address.', $responseData['message']);
    }

    public function test_invalid_credentials()
    {
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'invalid@example.com', 'password' => 'wrongpassword'])
            ->andReturn(false);

        $response = (new LoginService)->AuthLogin($this->mockRequest([
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]));

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('status', $responseData);
        $this->assertFalse($responseData['status']);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Credentials do not match', $responseData['message']);
    }

    private function mockRequest($data)
    {
        $request = \Mockery::mock('Illuminate\Http\Request');

        $request->shouldReceive('only')
            ->andReturn($data);

        $request->shouldReceive('validated')
            ->andReturn($data);

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
}
