<?php

namespace App\Http\Livewire;

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
    public array $cards = [0, 1, 2, 3, 5, 8, 13, 20, 40, 100];

    public ?int $vote = null;

    public Session $session;

    public ?Issue $currentIssue = null;

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
            $this->vote = Vote::whereUserId(auth()->id())
                ->whereIssueId($this->currentIssue->id)
                ->first()
                ?->value;
        }

        return view('livewire.voting-cards');
    }

    public function voteIssue(int $vote): void
    {
        if (Auth::user()) {
            Vote::query()->updateOrCreate([
                'user_id' => auth()->id(),
                'issue_id' => $this->currentIssue?->id,
            ], [
                'value' => $vote,
            ]);
            $this->vote = $vote;

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
