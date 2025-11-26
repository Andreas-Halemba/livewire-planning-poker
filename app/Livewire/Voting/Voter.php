<?php

namespace App\Livewire\Voting;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class Voter extends Component
{
    use InspectorLivewire;

    public Session $session;

    public function render(): View
    {
        // Ensure issues are loaded for the view
        if (!$this->session->relationLoaded('issues')) {
            $this->session->load('issues');
        }

        return view('livewire.voting.voter', [
            'session' => $this->session,
        ]);
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleVoteEvent',
            'refresh-voter-lists' => 'handleIssueEvent',
        ];
    }

    public function handleIssueEvent(): void
    {
        // Reload session with issues to ensure fresh data
        $this->session->load('issues');
    }

    public function handleVoteEvent(): void
    {
        // Reload session with issues and votes
        $this->session->load(['issues.votes']);
    }
}
