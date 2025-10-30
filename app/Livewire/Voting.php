<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Voting extends Component
{
    public Session $session;

    public string $inviteCode;

    public int $participantsCount = 0;

    public function mount(string $inviteCode): ?RedirectResponse
    {
        $this->inviteCode = $inviteCode;
        $this->session = Session::with('issues', 'users')->whereInviteCode($this->inviteCode)->firstOrFail();
        // Initialize with database count as fallback until SessionParticipants component updates it
        $this->participantsCount = $this->session->users->count();
        if (Auth::hasUser()) {
            $this->attachUserToSession();
        }
        return null;
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
            'participants-count-updated' => 'updateParticipantsCount',
        ];
    }

    public function handleIssueSelected(): void
    {
        // Reload session with issues to ensure fresh data
        $this->session->load('issues');
        $this->skipRender();
    }

    public function handleIssueCanceled(): void
    {
        // Reload session with issues to ensure fresh data
        $this->session->load('issues');
        $this->skipRender();
    }

    public function updateParticipantsCount(int $count): void
    {
        $this->participantsCount = $count;
        // Skip render as this is just updating a counter value that will be shown on next render
        $this->skipRender();
    }

    public function render(): View
    {
        // Ensure issues are loaded for the view
        if (!$this->session->relationLoaded('issues')) {
            $this->session->load('issues');
        }

        return view('livewire.voting');
    }

    private function attachUserToSession(): void
    {
        if (blank(Auth::user())) {
            return;
        }
        // if the user is already in the session, return
        if ($this->session->users->contains(Auth::user())) {
            return;
        }
        $this->session->users()->attach(Auth::user());
    }
}
