<?php

namespace App\Http\Livewire\Voting;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Voter extends Component
{
    public Session $session;

    public function render(): View
    {
        return view('livewire.voting.voter');
    }

    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => '$refresh',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => '$refresh',
        ];
    }
}
