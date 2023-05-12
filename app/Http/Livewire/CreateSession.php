<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Auth;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Redirector;

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

    public function createSession(): Redirector|RedirectResponse
    {
        $this->validate();
        $session = Session::create([
            'name' => $this->sessionName,
            'owner_id' => Auth::id(),
            'invite_code' => Str::random(8),
        ]);
        return Redirect::to(route('session.voting', ['inviteCode' => $session->invite_code], false));
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
