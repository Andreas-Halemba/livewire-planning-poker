<?php

namespace App\Http\Livewire;

use App\Events\UserJoins;
use App\Models\Session;
use App\Models\User;
use Auth;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Voting extends Component
{
    public Session $session;

    public string $inviteCode;

    public function mount(string $inviteCode): void
    {
        $this->session = Session::whereInviteCode($this->inviteCode)->firstOrFail();
        $this->attachUserToSession();
        // TODO: How to fix this auth()->user() being null?
        // @phpstan-ignore-next-line
        broadcast(new UserJoins($this->session, auth()->user()))->toOthers();
    }

    public function render(): View
    {
        return view('livewire.voting');
    }

    private function attachUserToSession(): void
    {
        if (auth()->user() && auth()->id() !== $this->session->owner_id && ! $this->session->users->contains(auth()->user())) {
            $this->session->users()->attach(auth()->user());
        }
    }
}
