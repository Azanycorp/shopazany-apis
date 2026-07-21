<?php

use App\Services\Auth\Auth;
use App\Services\Auth\RequestOptions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config([
        'services.payment_service.api_key' => 'test-api-key',
        'services.payment_service.api_secret' => 'test-api-secret',
        'security.auth_header_key' => 'X-Internal-Auth',
        'security.auth_header_value' => 'internal-secret',
    ]);

    /** @var Auth $this->auth */
    $this->auth = new Auth(resolve('config'));
});

// ------------------------------------------------------------------
// Legacy request() method
// ------------------------------------------------------------------

describe('request()', function () {
    it('sends requests with the configured internal auth header', function (string $method) {
        Http::fake();

        $this->auth->request($method, 'https://api.example.com/resource');

        Http::assertSent(function ($request) use ($method) {
            return strtoupper($request->method()) === strtoupper($method)
                && $request->hasHeader('X-Internal-Auth', 'internal-secret');
        });
    })->with(['get', 'post', 'put', 'patch', 'delete']);

    it('attaches a bearer token when one is provided', function () {
        Http::fake();

        $this->auth->request('post', 'https://api.example.com/resource', ['foo' => 'bar'], 'my-token');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer my-token')
                && $request['foo'] === 'bar';
        });
    });

    it('throws for an unsupported HTTP method', function () {
        $this->auth->request('trace', 'https://api.example.com/resource');
    })->throws(InvalidArgumentException::class, 'Unsupported method [trace]');
});

// ------------------------------------------------------------------
// sendRequest() / get|post|put|patch|delete()
// ------------------------------------------------------------------

describe('sendRequest() basics', function () {
    it('supports all verbs via the convenience methods', function (string $verb) {
        Http::fake();

        $this->auth->{$verb}('https://api.example.com/resource');

        Http::assertSent(fn ($request) => strtoupper($request->method()) === strtoupper($verb));
    })->with(['get', 'post', 'put', 'patch', 'delete']);

    it('sends default Accept and Content-Type headers', function () {
        Http::fake();

        $this->auth->get('https://api.example.com/status');

        Http::assertSent(function ($request) {
            return $request->header('Accept')[0] === 'application/json'
                && $request->header('Content-Type')[0] === 'application/json';
        });
    });

    it('accepts a plain array as shorthand for request data', function () {
        Http::fake();

        $this->auth->post('https://api.example.com/orders', ['sku' => 'ABC123']);

        Http::assertSent(fn ($request) => $request['sku'] === 'ABC123');
    });

    it('sends GET query parameters from options data', function () {
        Http::fake();

        $this->auth->get('https://api.example.com/search', new RequestOptions(
            data: ['q' => 'laravel', 'page' => 2],
        ));

        Http::assertSent(fn ($request) => $request['q'] === 'laravel' && $request['page'] === 2);
    });

    it('attaches a bearer token when one is provided via RequestOptions', function () {
        Http::fake();

        $this->auth->get('https://api.example.com/me', new RequestOptions(token: 'abc123'));

        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer abc123'));
    });

    it('lets custom headers override the defaults', function () {
        Http::fake();

        $this->auth->get('https://api.example.com/status', new RequestOptions(
            headers: ['Content-Type' => 'application/xml', 'X-Custom' => 'value'],
        ));

        Http::assertSent(function ($request) {
            return $request->header('Content-Type')[0] === 'application/xml'
                && $request->header('X-Custom')[0] === 'value';
        });
    });
});

// ------------------------------------------------------------------
// Auth header generation (X-API-KEY vs HMAC signature)
// ------------------------------------------------------------------

describe('auth header signing', function () {
    it('only sends X-API-KEY for GET requests (no HMAC headers)', function () {
        Http::fake();

        $this->auth->get('https://api.example.com/status');

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-KEY', 'test-api-key')
                && ! $request->hasHeader('X-TIMESTAMP')
                && ! $request->hasHeader('X-NONCE')
                && ! $request->hasHeader('X-SIGNATURE');
        });
    });

    it('only sends X-API-KEY for DELETE requests (no HMAC headers)', function () {
        Http::fake();

        $this->auth->delete('https://api.example.com/resource/1');

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-KEY', 'test-api-key')
                && ! $request->hasHeader('X-SIGNATURE');
        });
    });

    it('signs POST requests with a valid HMAC-SHA256 signature', function () {
        Http::fake();

        $payload = ['amount' => 1000, 'currency' => 'NGN'];

        $this->auth->post('https://api.example.com/charge', $payload);

        Http::assertSent(function ($request) use ($payload) {
            $timestamp = $request->header('X-TIMESTAMP')[0] ?? null;
            $nonce = $request->header('X-NONCE')[0] ?? null;
            $signature = $request->header('X-SIGNATURE')[0] ?? null;

            expect($timestamp)->not->toBeNull()->and($timestamp)->toBeNumeric();
            expect($nonce)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
            expect($signature)->not->toBeNull();

            $expectedBody = implode("\n", [
                $timestamp,
                'POST',
                'https://api.example.com/charge',
                json_encode($payload),
            ]);

            $expectedSignature = hash_hmac('sha256', $expectedBody, 'test-api-secret');

            expect($signature)->toBe($expectedSignature);

            return true;
        });
    });

    it('signs PUT and PATCH requests the same way as POST', function (string $verb) {
        Http::fake();

        $this->auth->{$verb}('https://api.example.com/resource/1', ['status' => 'active']);

        Http::assertSent(fn ($request) => $request->hasHeader('X-SIGNATURE')
            && $request->hasHeader('X-NONCE')
            && $request->hasHeader('X-TIMESTAMP'));
    })->with(['put', 'patch']);

    it('produces a different nonce (and therefore signature) on every call', function () {
        Http::fake();

        $this->auth->post('https://api.example.com/charge', ['amount' => 1000]);
        $this->auth->post('https://api.example.com/charge', ['amount' => 1000]);

        $nonces = (new Collection(Http::recorded()))
            ->map(fn (array $pair) => $pair[0]->header('X-NONCE')[0])
            ->unique();

        expect($nonces)->toHaveCount(2);
    });
});

// ------------------------------------------------------------------
// Retry behaviour
// ------------------------------------------------------------------

describe('retry behaviour', function () {
    it('retries a 500 and returns the successful response on the next attempt', function () {
        Http::fake([
            'api.example.com/*' => Http::sequence()
                ->push(['error' => 'server error'], 500)
                ->push(['ok' => true], 200),
        ]);

        $result = $this->auth->get('https://api.example.com/status', new RequestOptions(
            retries: 2,
            retryDelay: 0,
        ));

        expect($result)->toBeInstanceOf(Response::class)
            ->and($result->status())->toBe(200);

        Http::assertSentCount(2);
    });

    it('does not retry a 504 and returns a structured error array', function () {
        Http::fake([
            'api.example.com/*' => Http::response(['error' => 'gateway timeout'], 504),
        ]);

        $result = $this->auth->get('https://api.example.com/status', new RequestOptions(
            retries: 3,
            retryDelay: 0,
        ));

        expect($result)->toBeArray()
            ->and($result['status'])->toBeFalse()
            ->and($result['message'])->toContain('HTTP request returned status code 504');

        // shouldRetry() explicitly excludes 504, so only one attempt is made.
        Http::assertSentCount(1);
    });

    it('returns a structured error array once retries are exhausted on a persistent 503', function () {
        Http::fake([
            'api.example.com/*' => Http::response(['error' => 'unavailable'], 503),
        ]);

        $result = $this->auth->get('https://api.example.com/status', new RequestOptions(
            retries: 2,
            retryDelay: 0,
        ));

        expect($result)->toBeArray()
            ->and($result['status'])->toBeFalse()
            ->and($result['data'])->toBeNull();

        Http::assertSentCount(2);
    });

    it('retries on connection exceptions and eventually reports a structured error', function () {
        Http::fake(function () {
            throw new ConnectionException('Could not connect to server.');
        });

        $result = $this->auth->get('https://api.example.com/status', new RequestOptions(
            retries: 2,
            retryDelay: 0,
        ));

        expect($result)->toBeArray()
            ->and($result['status'])->toBeFalse()
            ->and($result['message'])->toContain('Could not connect to server.');
    });

    it('logs the error when a request ultimately fails', function () {
        Http::fake(function () {
            throw new ConnectionException('Timeout occurred');
        });

        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'HTTP Request Timeout')
                    && str_contains($context['error'], 'Timeout occurred');
            });

        $this->auth->get('https://api.example.com/status', new RequestOptions(
            retries: 1,
            retryDelay: 0,
        ));
    });
});

// ------------------------------------------------------------------
// Misc
// ------------------------------------------------------------------

describe('isSuccessful()', function () {
    it('reflects the underlying response status', function () {
        Http::fake([
            'api.example.com/ok' => Http::response(['ok' => true], 200),
            // 404 never touches the retry logic (only >=500 codes do), so we
            // get the raw response back to test against directly.
            'api.example.com/missing' => Http::response(['error' => 'not found'], 404),
        ]);

        $okResponse = $this->auth->get('https://api.example.com/ok');
        $missingResponse = $this->auth->get('https://api.example.com/missing');

        expect($this->auth->isSuccessful($okResponse))->toBeTrue()
            ->and($this->auth->isSuccessful($missingResponse))->toBeFalse();
    });
});

it('returns a structured error array (not a thrown exception) for an unsupported method in sendRequest', function () {
    $result = $this->auth->sendRequest('trace', 'https://api.example.com/resource');

    expect($result)->toBeArray()
        ->and($result['status'])->toBeFalse()
        ->and($result['message'])->toContain('Unsupported method [trace]');
});
