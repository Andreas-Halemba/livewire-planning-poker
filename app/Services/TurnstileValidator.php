<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileValidator
{
    private const SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function passes(string $token, ?string $remoteIp): bool
    {
        $secretKey = (string) config('services.turnstile.secret_key');

        if ($secretKey === '') {
            Log::warning('Turnstile registration validation could not be performed.', [
                'reason' => 'missing_secret_key',
            ]);

            return false;
        }

        try {
            $response = Http::asForm()
                ->connectTimeout(3)
                ->timeout(5)
                ->post(self::SITEVERIFY_URL, array_filter([
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]));
        } catch (ConnectionException $exception) {
            Log::warning('Turnstile registration validation request failed.', [
                'reason' => 'connection_failed',
                'exception' => $exception::class,
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Turnstile registration validation request failed.', [
                'reason' => 'unexpected_status',
                'status' => $response->status(),
            ]);

            return false;
        }

        $result = $response->json();

        if (! is_array($result) || ($result['success'] ?? false) !== true) {
            Log::warning('Turnstile registration validation failed.', [
                'reason' => 'invalid_token',
            ]);

            return false;
        }

        if (($result['action'] ?? null) !== config('services.turnstile.action')) {
            Log::warning('Turnstile registration validation failed.', [
                'reason' => 'unexpected_action',
            ]);

            return false;
        }

        if (($result['hostname'] ?? null) !== config('services.turnstile.expected_hostname')) {
            Log::warning('Turnstile registration validation failed.', [
                'reason' => 'unexpected_hostname',
            ]);

            return false;
        }

        return true;
    }
}
