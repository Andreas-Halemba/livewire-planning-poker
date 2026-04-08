<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Livewire + Echo set X-Socket-ID from Echo.socketId(). Before the websocket
 * connects, that value can be undefined on the client and arrive as the literal
 * string "undefined", which breaks Pusher/Reverb when excluding a socket from broadcasts.
 */
final class SanitizeBroadcastSocketIdHeader
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $socketId = $request->header('X-Socket-ID');

        if ($socketId === null || $socketId === '') {
            return $next($request);
        }

        $trimmed = trim($socketId);
        if ($trimmed === '' || strtolower($trimmed) === 'undefined' || strtolower($trimmed) === 'null') {
            $request->headers->remove('X-Socket-ID');
        }

        return $next($request);
    }
}
