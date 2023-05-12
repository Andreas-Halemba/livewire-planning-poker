<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Livewire\Component;

class JoinSession extends Component
{
    public string $inviteCode = '';

    protected $rules = [
        'inviteCode' => 'required|exists:sessions,invite_code',
    ];

    public function render()
    {
        return view('livewire.join-session');
    }

    public function joinSession()
    {
        $validatedData = $this->validate();
        $session = Session::where('invite_code', $validatedData['inviteCode'])->first();
        if (! $session->users->contains(auth()->user())) {
            $session->users()->attach(auth()->user());
        }
        redirect()->route('session.voting', ['inviteCode' => $validatedData['inviteCode']]);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
}
