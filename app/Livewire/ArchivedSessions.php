<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class ArchivedSessions extends Component
{
    use InspectorLivewire;

    /** @var Collection<int, Session> */
    public Collection $sessions;

    public function mount(): void
    {
        $this->sessions = collect();
    }

    public function render(): View
    {
        $ownerId = Auth::id();
        $this->sessions = $ownerId === null
            ? collect()
            : Session::query()
                ->whereOwnerId($ownerId)
                ->archived()
                ->orderByDesc('archived_at')
                ->get();

        return view('livewire.archived-sessions');
    }

    public function deleteSession(string $sessionId): void
    {
        if (! Auth::id()) {
            return;
        }

        $session = Session::whereOwnerId(Auth::id())->findOrFail($sessionId);
        $session->delete();
    }

    public function unarchiveSession(string $sessionId): void
    {
        $ownerId = Auth::id();
        if (! $ownerId) {
            return;
        }

        $session = Session::whereOwnerId($ownerId)
            ->archived()
            ->findOrFail($sessionId);

        $session->archived_at = null;
        $session->save();
    }
}
