<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;

class UserSessions extends Component
{
    public function render(): View
    {
        $sessions = [];
        if (auth()->user()) {
            $sessions = auth()->user()->sessions;
        }

        return view('livewire.user-sessions', [
            'sessions' => $sessions,
        ]);
    }

    // deleteSession method removes user from session
    public function deleteSession(string $sessionId): void
    {
        $session = Session::query()->findOrFail($sessionId);
        $session->users()->detach(auth()->user());
    }

    public function joinSession(string $inviteCode): RedirectResponse
    {
        $session = Session::where('invite_code', $inviteCode)->first();
        $user = auth()->user();
        $inviteCode = '';
        if ($user && $session) {
            $inviteCode = $session->invite_code;
            if ($user->id !== $session->owner_id && ! $session->users->contains($user)) {
                $session->users()->attach($user);
            }
        }

        return redirect()->route('session.voting', $inviteCode);
    }
}
