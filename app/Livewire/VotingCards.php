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
        }

        return view('livewire.voting-cards');
    }

    public function voteIssue(int|string $vote): void
    {
        if (Auth::user()) {
            // Convert '?' to null for database storage
            $voteValue = $vote === '?' ? null : (int) $vote;

            Vote::query()->updateOrCreate([
                'user_id' => auth()->id(),
                'issue_id' => $this->currentIssue?->id,
            ], [
                'value' => $voteValue,
            ]);
            $this->vote = $vote === '?' ? null : (int) $vote;

            broadcast(new HideVotes($this->session));
            broadcast(new AddVote($this->session, Auth::user()));
        }
    }

    public function removeVote(): void
    {
        Vote::whereUserId(auth()->id())->whereIssueId($this->currentIssue?->id)->delete();
        // @phpstan-ignore-next-line use can not be null here
        broadcast(new AddVote($this->session, auth()->user()));
        $this->vote = null;
    }
}
