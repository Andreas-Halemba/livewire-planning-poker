<?php

namespace App\Livewire;

use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

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

    public function joinSession(): void
    {
        $validatedData = $this->validate();
        $session = Session::whereInviteCode($validatedData['inviteCode'])->firstOrFail();
        app(SessionService::class)->joinSession($session);
        redirect()->to(route('session.voting', ['inviteCode' => $session->invite_code]));
    }

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
