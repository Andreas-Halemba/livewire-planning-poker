<?php

namespace App\Livewire;

use App\Models\Issue;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class UserVotes extends Component
{
    public Issue $issue;

    public Session $session;

    public function mount(Issue $issue): void
    {
        $this->issue = $issue;
        $this->session = $issue->session;
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => '$refresh',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => '$refresh',
        ];
    }

    /**
     * Summary of getUsersProperty
     * @return Collection<int, \App\Models\User>
     */
    public function getUsersProperty(): Collection
    {
        return $this->issue->session->users;
    }

    /**
     * Summary of getVotesProperty
     * @return Collection<int, \App\Models\Vote>
     */
    public function getVotesProperty(): Collection
    {
        return $this->issue->votes;
    }

    public function render(): View
    {
        return view('livewire.user-votes');
    }
}
