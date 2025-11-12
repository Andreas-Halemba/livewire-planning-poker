<?php

namespace App\Livewire;

use App\Events\AddVote;
use App\Events\HideVotes;
use App\Models\Issue;
use App\Models\Session;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class VotingCards extends Component
{
    use InspectorLivewire;

    /** @var array<int|string> */
    public array $cards = [1, 2, 3, 5, 8, 13, 21, 100, '?'];

    public ?int $vote = null;

    public int|string|null $selectedCard = null;

    public Session $session;

    public ?Issue $currentIssue = null;

    public ?int $selectedIssueId = null;

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
            'select-issue' => 'handleSelectIssue',
        ];
    }

    public function handleIssueSelected(): void
    {
        // Clear manual selection when issue is selected for voting
        $this->selectedIssueId = null;
        $this->selectedCard = null;
    }

    public function handleIssueCanceled(): void
    {
        // Reset when voting is canceled
        $this->selectedCard = null;
    }

    public function handleSelectIssue(int $issueId): void
    {
        $this->selectIssue($issueId);
    }

    public function selectIssue(int $issueId): void
    {
        $issue = Issue::whereSessionId($this->session->id)
            ->where('id', $issueId)
            ->first();

        // Allow selection if issue is not finished, or if user has already voted (to allow vote removal)
        if ($issue && ($issue->status !== Issue::STATUS_FINISHED || Vote::whereUserId(auth()->id())->whereIssueId($issue->id)->exists())) {
            $this->selectedIssueId = $issue->id;
            $this->dispatch('issue-selected', issueId: $issue->id);
        }
    }

    public function clearSelection(): void
    {
        $this->selectedIssueId = null;
        $this->selectedCard = null;
        $this->dispatch('issue-selection-cleared');
    }

    public function render(): View
    {
        // First check for active voting issue (STATUS_VOTING)
        $this->currentIssue = Issue::whereStatus(Issue::STATUS_VOTING)
            ->whereSessionId($this->session->id)
            ->first(['id', 'title', 'status', 'description', 'jira_key', 'jira_url']);

        // If there's an active voting, clear manual selection
        if ($this->currentIssue && $this->selectedIssueId) {
            $this->selectedIssueId = null;
        }

        // If no active voting, check for manually selected issue
        if (!$this->currentIssue && $this->selectedIssueId) {
            $selectedIssue = Issue::whereSessionId($this->session->id)
                ->where('id', $this->selectedIssueId)
                ->first(['id', 'title', 'status', 'description', 'jira_key', 'jira_url']);

            // Allow FINISHED issues only if user has already voted (to allow vote removal)
            if ($selectedIssue && ($selectedIssue->status !== Issue::STATUS_FINISHED || Vote::whereUserId(auth()->id())->whereIssueId($selectedIssue->id)->exists())) {
                $this->currentIssue = $selectedIssue;
            }
        }

        // Clear selection if selected issue no longer exists
        // Note: Don't clear if issue is FINISHED but user has a vote (to allow vote removal)
        if ($this->selectedIssueId && !$this->currentIssue) {
            $this->selectedIssueId = null;
        } elseif ($this->selectedIssueId && $this->currentIssue && $this->currentIssue->status === Issue::STATUS_FINISHED) {
            // Only clear if user doesn't have a vote on this finished issue
            if (!Vote::whereUserId(auth()->id())->whereIssueId($this->currentIssue->id)->exists()) {
                $this->selectedIssueId = null;
                $this->currentIssue = null;
            }
        }

        if ($this->currentIssue) {
            $userVote = Vote::whereUserId(auth()->id())
                ->whereIssueId($this->currentIssue->id)
                ->first();
            $this->vote = $userVote?->value;

            // If user has already voted, set selectedCard to match
            if ($this->vote !== null) {
                $this->selectedCard = $this->vote;
            } elseif ($userVote && $userVote->value === null) {
                // User voted with "?"
                $this->selectedCard = '?';
            }
        } else {
            // Reset selection when no current issue
            $this->selectedCard = null;
        }

        return view('livewire.voting-cards');
    }

    public function selectCard(int|string $card): void
    {
        // Only allow selection if user hasn't voted yet
        if (!$this->hasVoted()) {
            // Normalize the card value - handle both string and int
            if ($card === '?' || $card === "?") {
                $this->selectedCard = '?';
            } else {
                $this->selectedCard = (int) $card;
            }
        }
    }

    public function confirmVote(): void
    {
        if (!$this->selectedCard || !Auth::user() || !$this->currentIssue) {
            return;
        }

        // Convert '?' to null for database storage
        $voteValue = $this->selectedCard === '?' ? null : (int) $this->selectedCard;

        Vote::query()->updateOrCreate([
            'user_id' => auth()->id(),
            'issue_id' => $this->currentIssue->id,
        ], [
            'value' => $voteValue,
        ]);

        $this->vote = $this->selectedCard === '?' ? null : (int) $this->selectedCard;

        // Only broadcast HideVotes if there's an active voting session
        // (STATUS_VOTING). For async voting, we just notify about the new vote.
        if ($this->currentIssue->status === Issue::STATUS_VOTING) {
            broadcast(new HideVotes($this->session))->toOthers();
        }
        broadcast(new AddVote($this->session, Auth::user()))->toOthers();
    }

    public function removeVote(): void
    {
        if (!Auth::user() || !$this->currentIssue) {
            return;
        }

        // Reload issue to get current status
        $issue = Issue::findOrFail($this->currentIssue->id);
        $originalStatus = $issue->status;

        // Delete the vote
        Vote::whereUserId(auth()->id())->whereIssueId($issue->id)->delete();

        // Only reset issue status to VOTING if it was previously in a voting state
        // (e.g., STATUS_FINISHED). For async voting (STATUS_NEW), keep the status unchanged.
        if ($originalStatus === Issue::STATUS_FINISHED) {
            $issue->status = Issue::STATUS_VOTING;
            $issue->save();
        }

        // Only broadcast HideVotes if there's an active voting session
        // (STATUS_VOTING). For async voting, we just notify about the vote removal.
        if ($originalStatus === Issue::STATUS_VOTING || $issue->status === Issue::STATUS_VOTING) {
            broadcast(new HideVotes($this->session))->toOthers();
        }

        // Send AddVote event to update vote status
        // @phpstan-ignore-next-line use can not be null here
        broadcast(new AddVote($this->session, auth()->user()))->toOthers();

        $this->vote = null;
        $this->selectedCard = null;
    }

    private function hasVoted(): bool
    {
        if (!$this->currentIssue) {
            return false;
        }

        return Vote::whereUserId(auth()->id())
            ->whereIssueId($this->currentIssue->id)
            ->exists();
    }
}
