<?php

namespace App\Http\Livewire;

use App\Events\UserJoins;
use App\Models\Session;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class Voting extends Component
{
    public Session $session;

    public string $inviteCode;

    public function mount(string $inviteCode): void
    {
        $this->inviteCode = $inviteCode;
        $this->session = Session::whereInviteCode($this->inviteCode)->firstOrFail();
        $user = auth()->user();
        if ($user) {
            $this->attachUserToSession($user);
            broadcast(new UserJoins($this->session, $user))->toOthers();
        }
    }

    public function render(): View
    {
        return view('livewire.voting');
    }

    private function attachUserToSession(User $user): void
    {
        if ($user->id !== $this->session->owner_id && ! $this->session->users->contains($user)) {
            $this->session->users()->attach($user);
        }
    }
}
