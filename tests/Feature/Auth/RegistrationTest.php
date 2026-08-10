<?php

use App\Providers\RouteServiceProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function () {
    config()->set([
        'services.turnstile.site_key' => 'test-site-key',
        'services.turnstile.secret_key' => 'test-secret-key',
        'services.turnstile.expected_hostname' => 'livewire-planning-poker.test',
        'services.turnstile.action' => 'register',
    ]);
});

afterEach(function () {
    RateLimiter::clear(md5('registration127.0.0.1'));
});

test('registration screen can be rendered', function () {
    $response = get('/register');

    $response->assertSuccessful()
        ->assertSee('cf-turnstile', false)
        ->assertSee('test-site-key');
});

test('new users can register', function () {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
            'action' => 'register',
            'hostname' => 'livewire-planning-poker.test',
        ]),
    ]);

    $response = post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'valid-token',
    ]);

    assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);

    Http::assertSentCount(1);
});

test('registration requires a turnstile token', function () {
    $response = post('/register', registrationData());

    assertGuest();
    $response->assertSessionHasErrors('cf-turnstile-response');
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});

test('registration rejects a failed turnstile validation', function (array $turnstileResponse) {
    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response($turnstileResponse),
    ]);

    $response = post('/register', registrationData([
        'cf-turnstile-response' => 'invalid-token',
    ]));

    assertGuest();
    $response->assertSessionHasErrors('cf-turnstile-response');
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
})->with([
    'invalid token' => [['success' => false]],
    'expired or reused token' => [['success' => false, 'error-codes' => ['timeout-or-duplicate']]],
    'unexpected action' => [[
        'success' => true,
        'action' => 'login',
        'hostname' => 'livewire-planning-poker.test',
    ]],
    'unexpected hostname' => [[
        'success' => true,
        'action' => 'register',
        'hostname' => 'attacker.example',
    ]],
]);

test('registration is rejected when turnstile cannot be reached', function () {
    Http::fake(function () {
        throw new ConnectionException('Turnstile is unavailable.');
    });

    $response = post('/register', registrationData([
        'cf-turnstile-response' => 'valid-token',
    ]));

    assertGuest();
    $response->assertSessionHasErrors('cf-turnstile-response');
    $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
});

test('registration is limited to five attempts per IP address every ten minutes', function () {
    foreach (range(1, 5) as $attempt) {
        post('/register', registrationData([
            'email' => "attempt-{$attempt}@example.com",
        ]))->assertRedirect();
    }

    post('/register', registrationData([
        'email' => 'blocked@example.com',
    ]))->assertTooManyRequests();
});

/**
 * @return array<string, string>
 */
function registrationData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}
