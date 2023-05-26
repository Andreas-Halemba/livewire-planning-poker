<?php

use function Pest\Laravel\get;

it('redirects to dashboard', function () {
    get('/')->assertRedirectContains('dashboard');
});
