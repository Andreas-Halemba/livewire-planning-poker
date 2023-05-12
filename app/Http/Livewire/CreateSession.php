<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateSession extends Component
{
    public string $sessionName = '';

    protected $rules = [
        'sessionName' => 'required|string|min:2|max:255',
    ];

    public function render()
    {
        return view('livewire.create-session');
    }

    public function createSession()
    {
        $this->validate();

        $session = Session::create([
            'name' => $this->sessionName,
            'owner_id' => auth()->user()->id,
            'invite_code' => Str::random(8),
        ]);

        return redirect()->route('session.issues', $session->invite_code);
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }
}
