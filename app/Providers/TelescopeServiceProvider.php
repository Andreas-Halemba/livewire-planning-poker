<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry) {
            if (app()->isLocal() || app()->hasDebugModeEnabled()) {
                return true;
            }

            return $entry->isReportableException()
                   || $entry->isFailedRequest()
                   || $entry->isFailedJob()
                   || $entry->isScheduledTask()
                   || $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if (app()->isLocal()) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function ($user) {
            $allowedEmails = array_filter(
                explode(',', (string) env('TELESCOPE_ALLOWED_EMAILS', '')),
                fn($email) => ! empty(trim($email)),
            );

            return in_array($user->email, $allowedEmails);
        });
    }
}
