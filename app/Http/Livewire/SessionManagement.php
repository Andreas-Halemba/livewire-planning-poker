<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Livewire\Component;
use Redirect;

class SessionManagement extends Component
{
    public string $sessionName;

    public string $inviteCode;

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

        $session = Session::where('invite_code', $this->inviteCode)->first();
        $inviteCode = '';

        $user = auth()->user();
        if ($user && $session) {
            $inviteCode = $session->invite_code;
            if ($user->id !== $session->owner_id && ! $session->users->contains($user)) {
                $session->users()->attach($user);
            }
        }

        return redirect()->route('session.voting', $inviteCode);
    }
}
