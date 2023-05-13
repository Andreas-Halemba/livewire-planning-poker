<?php

namespace App\Http\Livewire;

use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use Livewire\Redirector;

class JoinSession extends Component
{
    public string $inviteCode = '';


    protected array $rules = [
        'inviteCode' => 'required|exists:sessions,invite_code',
    ];

    public function render(): View
    {
        return view('livewire.join-session');
    }

    public function joinSession(): Redirector|RedirectResponse
    {
        $validatedData = $this->validate();
        $session = Session::whereInviteCode($validatedData['inviteCode'])->firstOrFail();
        app(SessionService::class)->joinSession($session);
        return Redirect::to(route('session.voting', ['inviteCode' => $validatedData['inviteCode']], false));
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
