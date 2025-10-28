<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define(
            'owns_session',
            fn(User $user, Session $session) => $user->id === $session->owner_id,
        );
        Gate::define(
            'vote_session',
            fn(User $user, Session $session) => $session->users->contains($user),
        );
    }
}
