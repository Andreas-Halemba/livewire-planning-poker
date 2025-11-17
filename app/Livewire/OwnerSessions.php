<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class OwnerSessions extends Component
{
    use InspectorLivewire;

    /** @var Collection<int, \App\Models\Session> */
    public Collection $sessions;

    public function render(): View
    {
        $this->sessions = Session::query()
            ->whereOwnerId(Auth::id())
            ->active()
            ->get();

        return view('livewire.owner-sessions');
    }

    public function deleteSession(string $sessionId): void
    {
        $session = Session::whereOwnerId(Auth::id())->findOrFail($sessionId);
        $session->delete();
    }

    public function archiveSession(string $sessionId): void
    {
        $session = Session::whereOwnerId(Auth::id())->findOrFail($sessionId);

        if ($session->archived_at !== null) {
            return;
        }

        $session->archived_at = now();
        $session->save();
    }
}
