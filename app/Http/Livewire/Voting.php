<?php

namespace App\Http\Livewire;

use App\Events\UserJoins;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Livewire\Redirector;

class Voting extends Component
{
    public Session $session;

    public string $inviteCode;

    public function mount(string $inviteCode): Redirector|RedirectResponse|null
    {
        $this->inviteCode = $inviteCode;
        try {
            $this->session = Session::whereInviteCode($this->inviteCode)->firstOrFail();
        } catch (ModelNotFoundException $th) {
            return redirect()->to(route('dashboard'));
        }
        $user = auth()->user();
        if ($user) {
            $this->attachUserToSession();
            broadcast(new UserJoins($this->session, $user))->toOthers();
        }
        return null;
    }

    public function render(): View
    {
        return view('livewire.voting');
    }

    private function attachUserToSession(): void
    {
        if (auth()->user() && auth()->id()
            !== $this->session->owner_id
            && ! $this->session->users->contains(auth()->user())
        ) {
            $this->session->users()->attach(auth()->user());
        }
    }
}
