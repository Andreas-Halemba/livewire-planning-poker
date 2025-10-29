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

    public function mount(string $inviteCode): ?RedirectResponse
    {
        $this->inviteCode = $inviteCode;
        $this->session = Session::whereInviteCode($this->inviteCode)->firstOrFail();
        if (Auth::hasUser()) {
            $this->attachUserToSession();
        }
        return null;
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => '$refresh',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => '$refresh',
        ];
    }

    public function render(): View
    {
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
