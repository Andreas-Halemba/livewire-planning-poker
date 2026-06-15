<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Inspector\Inspector;
use Inspector\Laravel\Facades\Inspector as InspectorFacade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', fn(User $user) => app()->environment('local') || $user->email === 'andreas.halemba@iu.org');

        Log::shareContext([
            'app_version' => config('app.version'),
        ]);

        if (app()->bound('inspector')) {
            InspectorFacade::beforeFlush(function (Inspector $inspector): void {
                if ($inspector->hasTransaction()) {
                    $inspector->transaction()->addContext('App', [
                        'version' => config('app.version'),
                    ]);
                }
            });
        }
    }
}
