<?php

namespace App\Http\Livewire;

use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Redirect;

class SessionManagement extends Component
{
    public string $sessionName;

    public string $inviteCode;
    public string $inviteCode;

    protected array $rules = [
    protected array $rules = [
        'sessionName' => 'required|min:3|max:255',
    ];

    public function render(): View
    public function render(): View
    {
        return view('livewire.session-management');
    }

    public function joinSession(): RedirectResponse
    public function joinSession(): RedirectResponse
    {
        $this->validate([
            'inviteCode' => 'required|exists:sessions,invite_code',
        ]);
        $session = Session::whereInviteCode($this->inviteCode)->firstOrFail();
        app(SessionService::class)->joinSession($session);
        return redirect()->intended(route('session.voting', $session->invite_code));
    }
}
