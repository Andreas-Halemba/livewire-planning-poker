<?php

namespace App\Services;

use App\Models\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SessionService
{
    public function __construct()
    {
    }

    public function createSession(): Session
    {
        if(Auth::user()) {
            return Session::create([
                'owner_id' => Auth::user()->id,
                'invite_code' => Str::random(8),
            ]);
        } else {
            abort(403);
        }
    }

    public function joinSession(Session $session): void
    {
        if(Auth::user()) {
            $session->users()->syncWithoutDetaching(Auth::user());
        }
    }

    public function leaveSession(Session $session): void
    {
        if(Auth::user()) {
            $session->users()->detach(Auth::user());
        }
    }
}
