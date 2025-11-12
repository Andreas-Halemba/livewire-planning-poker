<?php

namespace App\Livewire;

use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class SessionManagement extends Component
{
    use InspectorLivewire;

    public string $sessionName;

    public string $inviteCode;

    /** @var array<string, string> */
    protected array $rules = [
        'sessionName' => 'required|min:3|max:255',
    ];

    public function render(): View
    {
        return view('livewire.session-management');
    }

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
