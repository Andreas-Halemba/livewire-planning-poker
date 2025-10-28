<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OwnerSessions extends Component
{
    /** @var Collection<int, \App\Models\Session> */
    public Collection $sessions;

    public function render(): View
    {
        $this->sessions = Session::query()->whereOwnerId(Auth::id())->get();

        return view('livewire.owner-sessions');
    }

    public function deleteSession(string $sessionId): void
    {
        $session = Session::findOrFail($sessionId);
        $session->delete();
    }
}
