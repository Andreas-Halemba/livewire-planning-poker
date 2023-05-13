<?php

namespace App\Http\Livewire;

use App\Models\Session;
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

    public function createSession(): void
    {
        $this->validate();
        $session = Session::query()->create([
            'name' => $this->sessionName,
            'owner_id' => auth()->user()?->id,
            'invite_code' => Str::random(8),
        ]);
        if($session) {
            redirect()->to(route('session.voting', ['inviteCode' => $session->invite_code]));
        }
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
