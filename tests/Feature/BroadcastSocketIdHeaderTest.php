<?php

declare(strict_types=1);

use App\Http\Middleware\SanitizeBroadcastSocketIdHeader;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('removes literal undefined X-Socket-ID so Pusher does not receive an invalid socket', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_X_SOCKET_ID' => 'undefined',
    ]);

    $middleware = app(SanitizeBroadcastSocketIdHeader::class);

    $seen = null;
    $middleware->handle($request, function (Request $req) use (&$seen): Response {
        $seen = $req->header('X-Socket-ID');

        return response('ok');
    });

    expect($seen)->toBeNull();
});

it('preserves a valid X-Socket-ID', function () {
    $request = Request::create('/test', 'GET', [], [], [], [
        'HTTP_X_SOCKET_ID' => '123.456',
    ]);

    $middleware = app(SanitizeBroadcastSocketIdHeader::class);

    $seen = null;
    $middleware->handle($request, function (Request $req) use (&$seen): Response {
        $seen = $req->header('X-Socket-ID');

        return response('ok');
    });

    expect($seen)->toBe('123.456');
});
