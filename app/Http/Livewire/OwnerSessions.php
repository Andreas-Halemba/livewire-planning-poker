<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OwnerSessions extends Component
{
    public Collection $sessions;

    public function render()
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
