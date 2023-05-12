<?php

namespace App\Http\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class VotingIssueList extends Component
{
    public Collection $issues;

    public Session $session;

    public function getListeners(): array
    {
        return [
            "echo:session.{$this->session->invite_code},.IssueAdded" => '$refresh',
            "echo:session.{$this->session->invite_code},.IssueDeleted" => '$refresh',
        ];
    }

    public function render(): View
    {
        return view('livewire.voting-issue-list');
    }
}
