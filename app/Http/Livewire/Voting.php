<?php

namespace App\Http\Livewire;

use App\Events\UserJoins;
use App\Models\Session;
use App\Models\User;
use Livewire\Component;

class Voting extends Component
{
    public Session $session;

    public string $inviteCode;

    public function mount(string $inviteCode)
    {
        $this->inviteCode = $inviteCode;
        $this->session = Session::whereInviteCode($this->inviteCode)->firstOrFail();
        $this->attachUserToSession(auth()->user());
        broadcast(new UserJoins($this->session, auth()->user()))->toOthers();
    }
    
    public function render()
    {
        return view('livewire.voting');
    }

    private function attachUserToSession(User $user)
    {
        if ($user->id !== $this->session->owner_id && ! $this->session->users->contains($user)) {
            $this->session->users()->attach($user);
        }
    }
}
