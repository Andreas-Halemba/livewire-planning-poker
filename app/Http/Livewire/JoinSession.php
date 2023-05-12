<?php

namespace App\Http\Livewire;

use App\Models\Session;
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
        $session = Session::where('invite_code', $validatedData['inviteCode'])->first();
        $user = auth()->user();

        if ($session && $user) {
            if (! $session->users->contains($user)) {
                $session->users()->attach($user);
            }
        }

        redirect()->route('session.voting', ['inviteCode' => $validatedData['inviteCode']]);
    }

    public function updated(mixed $propertyName): void
    {
        $this->validateOnly($propertyName);
    }
}
