<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserSessions extends Component
{
    public function render(): View
    {
        $sessions = collect([]);
        if (Auth::user()) {
            $sessions = Auth::user()->sessions;
        }
        return view('livewire.user-sessions', [
            'sessions' => $sessions,
        ]);
    }

    public function leaveSession(int $sessionId): void
    {
        Session::whereId($sessionId)->first()?->users()->detach(Auth::user());
    }

    public function joinSession(string $inviteCode): void
    {
        $session = Session::whereInviteCode($inviteCode)->firstOrFail();
        if (Auth::id() !== $session->owner_id && ! $session->users->contains(Auth::user() ?? '')) {
            $session->users()->attach(Auth::user());
        }
        redirect()->to(route('session.voting', ['inviteCode' => $session->invite_code]));
    }
}
