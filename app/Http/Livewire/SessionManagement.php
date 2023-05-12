<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Livewire\Component;

class SessionManagement extends Component
{
    public $sessionName;

    public $inviteCode;

    protected $rules = [
        'sessionName' => 'required|min:3|max:255',
    ];

    public function render()
    {
        return view('livewire.session-management');
    }

    public function joinSession()
    {
        $this->validate([
            'inviteCode' => 'required|exists:sessions,invite_code',
        ]);

        $session = Session::where('invite_code', $this->inviteCode)->first();
        if (auth()->user()->id !== $session->owner_id && ! $session->users->contains(auth()->user())) {
            $session->users()->attach(auth()->user());
        }

        return redirect()->route('session.voting', $session->invite_code);
    }
}
