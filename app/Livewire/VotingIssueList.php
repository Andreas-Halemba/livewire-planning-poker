<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Attributes\Computed;
use Livewire\Component;

class VotingIssueList extends Component
{
    use InspectorLivewire;

    public Session $session;

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueChange',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueChange',
        ];
    }

    public function handleIssueChange(): void
    {
        // Refresh the session to clear cached relationships
        $this->session->refresh();

        // Clear the computed property cache
        unset($this->issues);
    }

    #[Computed]
    /**
     * @return Collection<int, \App\Models\Issue>
     */
    public function issues(): Collection
    {
        return $this->session->issues()->get();
    }

    public function render(): View
    {
        return view('livewire.voting-issue-list');
    }
}
