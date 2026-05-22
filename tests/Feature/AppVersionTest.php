<?php

use App\Models\User;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('exposes a version string via config', function () {
    $version = config('app.version');

    expect($version)->toBeString()->not->toBeEmpty();
    expect($version)->toMatch('/^(\d+\.\d+\.\d+|dev.*)$/');
});

it('renders the version meta tag and footer on the guest layout', function () {
    $version = config('app.version');

    $response = get(route('login'));

    $response->assertOk();
    $response->assertSee('<meta name="app-version" content="' . $version . '">', false);
    $response->assertSee('v' . $version);
});

it('renders the version meta tag and footer on the app layout', function () {
    $version = config('app.version');
    $user = User::factory()->create();

    $response = actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('<meta name="app-version" content="' . $version . '">', false);
    $response->assertSee('v' . $version);
});

it('shares the app version as default log context', function () {
    $context = Log::sharedContext();

    expect($context)->toHaveKey('app_version', config('app.version'));
});
