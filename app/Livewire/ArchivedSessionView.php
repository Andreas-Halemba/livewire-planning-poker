<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;

class ArchivedSessionView extends Component
{
    use InspectorLivewire;

    public Session $session;

    public bool $isOwner = false;

    public function mount(string $inviteCode): RedirectResponse|LivewireRedirector|null
    {
        $this->session = Session::with(['issues' => function ($query) {
            $query->orderByDesc('storypoints')->orderBy('title');
        }, 'users'])->whereInviteCode($inviteCode)->firstOrFail();

        // Bail out if the session is not archived
        if ($this->session->archived_at === null) {
            return Redirect::route('session.voting', $inviteCode);
        }

        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $isParticipant = $this->session->users->contains('id', $user->id);
        $this->isOwner = $user->id === $this->session->owner_id;

        if (! $this->isOwner && ! $isParticipant) {
            abort(403);
        }

        return null;
    }

    public function unarchiveSession(): RedirectResponse|LivewireRedirector
    {
        if (! $this->isOwner) {
            abort(403);
        }

        $this->session->archived_at = null;
        $this->session->save();
        $this->dispatch('sessions-updated');

        return Redirect::route('session.voting', $this->session->invite_code);
    }

    public function render(): View
    {
        return view('livewire.archived-session-view');
    }
}
