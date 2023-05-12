<?php

namespace App\Http\Livewire\Voting;

use App\Models\Session;
use Livewire\Component;

class Voter extends Component
{
    public Session $session;

    public array $cards = ['0', '1', '2', '3', '5', '8', '13', '20', '40', '100'];

    public function render()
    {
        return view('livewire.voting.voter');
    }
}
