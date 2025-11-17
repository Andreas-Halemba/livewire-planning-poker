<?php

namespace App\Livewire;

use App\Livewire\Concerns\ManagesSessions;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class ArchivedSessions extends Component
{
    use InspectorLivewire;
    use ManagesSessions;

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
        $this->ownedSessionOrFail($sessionId)->delete();
        $this->dispatchSessionsUpdated();
    }

    public function unarchiveSession(string $sessionId): void
    {
        $session = $this->ownedSessionOrFail($sessionId);

        if ($session->archived_at === null) {
            return;
        }

        $session->archived_at = null;
        $session->save();
        $this->dispatchSessionsUpdated();
    }
}
