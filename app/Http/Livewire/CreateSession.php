<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Livewire\Component;

class CreateSession extends Component
{
    use AuthorizesRequests;

    public string $sessionName = '';

    protected array $rules = [
        'sessionName' => 'required|string|min:2|max:255',
    ];

    public function render(): View
    {
        return view('livewire.create-session');
    }

    public function createSession(): RedirectResponse
    {
        $this->validate();
        $session = Session::create([
            'name' => $this->sessionName,
            'owner_id' => Auth::id(),
            'invite_code' => Str::random(8),
        ]);
        return redirect(route('session.issues', $session->invite_code));
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
