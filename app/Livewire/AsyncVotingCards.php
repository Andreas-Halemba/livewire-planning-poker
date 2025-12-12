<?php

namespace App\Livewire;

use App\Enums\IssueStatus;
use App\Events\AsyncVoteUpdated;
use App\Models\Issue;
use App\Models\Session;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Async Voting Cards (v2-style)
 *
 * Dedicated component for the async flow (voters only):
 * - select an issue (without starting a live round)
 * - pick a card and save a vote
 * - remove vote
 *
 * Intentionally keeps logic smaller than the existing `VotingCards` component.
 */
class AsyncVotingCards extends Component
{
    public Session $session;

    public ?Issue $selectedIssue = null;

    public ?int $selectedCard = null;

    public ?int $myVote = null;

    /** @var array<int> */
    public array $cards = [1, 2, 3, 5, 8, 13, 21, 100];

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            'async-select-issue' => 'selectIssue',
            // If PO starts live voting, clear async selection
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'clearSelection',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'clearSelection',
        ];
    }

    public function selectIssue(int $issueId): void
    {
        if (!Auth::check()) {
            return;
        }

        $issue = Issue::query()
            ->where('session_id', $this->session->id)
            ->where('id', $issueId)
            ->first();

        if (!$issue) {
            return;
        }

        // Async only: ignore finished + live voting issues
        if ($issue->status === IssueStatus::FINISHED || $issue->status === IssueStatus::VOTING) {
            return;
        }

        $this->selectedIssue = $issue;
        $this->selectedCard = null;

        $vote = Vote::query()
            ->where('user_id', Auth::id())
            ->where('issue_id', $issue->id)
            ->first();

        $this->myVote = $vote?->value;
    }

    public function chooseCard(int $card): void
    {
        $this->selectedCard = $card;
    }

    public function saveVote(): void
    {
        if (!Auth::check() || !$this->selectedIssue || $this->selectedCard === null) {
            return;
        }

        Vote::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'issue_id' => $this->selectedIssue->id,
            ],
            [
                'value' => $this->selectedCard,
            ],
        );

        $this->myVote = $this->selectedCard;

        broadcast(new AsyncVoteUpdated(
            $this->session->invite_code,
            $this->selectedIssue->id,
            Auth::id(),
            true,
        ))->toOthers();

        $this->dispatch('refresh-async-lists');

        // After saving, clear selection so no issue stays "active" in the async panel.
        $this->clearSelection();
    }

    public function removeVote(): void
    {
        if (!Auth::check() || !$this->selectedIssue) {
            return;
        }

        Vote::query()
            ->where('user_id', Auth::id())
            ->where('issue_id', $this->selectedIssue->id)
            ->delete();

        $this->myVote = null;
        $this->selectedCard = null;

        broadcast(new AsyncVoteUpdated(
            $this->session->invite_code,
            $this->selectedIssue->id,
            Auth::id(),
            false,
        ))->toOthers();

        $this->dispatch('refresh-async-lists');
    }

    public function clearSelection(): void
    {
        $this->selectedIssue = null;
        $this->selectedCard = null;
        $this->myVote = null;
    }

    public function render(): View
    {
        return view('livewire.async-voting-cards');
    }
}
