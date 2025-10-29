<?php

namespace App\Livewire;

use App\Events\AddVote;
use App\Events\HideVotes;
use App\Models\Issue;
use App\Models\Session;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VotingCards extends Component
{
    /** @var array<int|string> */
    public array $cards = [0, 1, 2, 3, 5, 8, 13, 21, 100, '?'];

    public ?int $vote = null;

    public int|string|null $selectedCard = null;

    public Session $session;

    public ?Issue $currentIssue = null;

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => '$refresh',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => '$refresh',
        ];
    }

    public function render(): View
    {
        $this->currentIssue = Issue::whereStatus(Issue::STATUS_VOTING)
            ->whereSessionId($this->session->id)
            ->first(['id', 'title']);
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

        broadcast(new HideVotes($this->session));
        broadcast(new AddVote($this->session, Auth::user()));
    }

    public function removeVote(): void
    {
        if (!Auth::user() || !$this->currentIssue) {
            return;
        }

        // Reload issue to get current status
        $issue = Issue::findOrFail($this->currentIssue->id);

        // Delete the vote
        Vote::whereUserId(auth()->id())->whereIssueId($issue->id)->delete();

        // If issue status is not voting, reset it to voting
        if ($issue->status !== Issue::STATUS_VOTING) {
            $issue->status = Issue::STATUS_VOTING;
            $issue->save();
        }

        // Send HideVotes event to reset votes revealed state for all users
        broadcast(new HideVotes($this->session));

        // Send AddVote event to update vote status
        // @phpstan-ignore-next-line use can not be null here
        broadcast(new AddVote($this->session, auth()->user()));

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
