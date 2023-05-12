<?php

namespace App\Http\Livewire;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class UserVotes extends Component
{
    public Issue $issue;

    public function mount(Issue $issue)
    {
        $this->issue = $issue;
    }

    public function getUsersProperty(): Collection
    {
        return $this->issue->session->users;
    }

    public function getVotesProperty(): Collection
    {
        return $this->issue->votes;
    }

    public function render()
    {
        return view('livewire.user-votes');
    }
}
