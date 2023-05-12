<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class UserSessions extends Component
{
    public function render(): View
    {
        $sessions = auth()->user()->sessions;

        return view('livewire.user-sessions', [
            'sessions' => $sessions,
        ]);
    }

    // deleteSession method removes user from session
    public function deleteSession($sessionId): void
    {
        $session = Session::query()->findOrFail($sessionId);
        $session->users()->detach(auth()->user());
    }

    public function joinSession($inviteCode)
    {
        $session = Session::where('invite_code', $inviteCode)->first();
        if (auth()->user()->id !== $session->owner_id && ! $session->users->contains(auth()->user())) {
            $session->users()->attach(auth()->user());
        }

        return redirect()->route('session.voting', $session->invite_code);
    }
}
