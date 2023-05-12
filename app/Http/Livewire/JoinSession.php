<?php

namespace App\Http\Livewire;

use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JoinSession extends Component
{
    public string $inviteCode = '';

    private SessionService $sessionService;

    protected array $rules = [
        'inviteCode' => 'required|exists:sessions,invite_code',
    ];

    public function mount(): void
    {
        $this->sessionService = app(SessionService::class);
    }

    public function render(): View
    {
        return view('livewire.join-session');
    }

    public function joinSession(): void
    {
        $validatedData = $this->validate();
        $session = Session::whereInviteCode($validatedData['inviteCode'])->firstOrFail();
        $this->sessionService->joinSession($session);
        redirect(route('session.voting', ['inviteCode' => $validatedData['inviteCode']]))->send();
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
