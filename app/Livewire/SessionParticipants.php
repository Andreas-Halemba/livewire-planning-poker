<?php

namespace App\Livewire;

use App\Events\RevealVotes;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SessionParticipants extends Component
{
    public Session $session;

    /** @var Collection<int|string, \App\Models\User> */
    public Collection $participants;

    public ?Issue $issue;

    /** @var array<int|string, mixed> */
    public array $votes = [];

    /** @var array<string, bool> */
    public array $participantsVoted = [];

    public bool $votesRevealed = false;

    public function mount(): void
    {
        $this->participants = collect([]);
        if (Auth::user()) {
            $this->participants->push(Auth::user());
        }
    }

    public function render(): View
    {
        $this->updateIssueData();
        return view('livewire.session-participants');
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'revealVotes',
            "echo-presence:session.{$this->session->invite_code},.HideVotes" => 'hideVotes',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'newVote',
            "echo-presence:session.{$this->session->invite_code},here" => 'updateUsers',
            "echo-presence:session.{$this->session->invite_code},joining" => 'userJoins',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'userLeaves',
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'updateCurrentIssue',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'unsetCurrentIssue',
        ];
    }

    public function updateCurrentIssue(Issue $issue): void
    {
        $this->issue = $issue;
    }

    public function unsetCurrentIssue(): void
    {
        $this->issue = null;
        $this->reset('votes', 'votesRevealed');
    }


    /**
     * User joins the session.
     * @param array<int|string, mixed> $user
     */
    public function userJoins(array $user): void
    {
        if ($user['id'] === Auth::id() || $this->participants->contains('id', $user['id'])) {
            return;
        }
        $this->participants->push(User::whereId($user['id'])->firstOrFail());
    }

    public function userLeaves(User $user): void
    {
        $this->participants = $this->participants->filter(fn(User $participant) => $participant->id !== $user->id);
    }

    /** @param array<int|string, mixed> $users */
    public function updateUsers(array $users): void
    {
        $this->participants = collect($users)->map(fn(array $user): User => User::whereId($user['id'])->firstOrFail());
    }

    public function revealVotes(): void
    {
        if ($this->issue) {
            $this->votes = Vote::query()->whereBelongsTo($this->issue)->get()->pluck('value', 'user_id')->toArray();
            $this->votesRevealed = true;
        }
    }

    public function hideVotes(): void
    {
        $this->votes = [];
        $this->votesRevealed = false;
    }

    public function newVote(User $user): void
    {
        $this->votes[$user->id] = 'X';
    }

    public function sendRevealEvent(): void
    {
        $this->revealVotes();
        broadcast(new RevealVotes($this->session))->toOthers();
    }

    public function userDidVote(string $id): bool
    {
        return Arr::has($this->votes, $id) && $this->votes[$id] !== null;
    }

    private function updateIssueData(): void
    {
        if ($this->session->currentIssue()) {
            $this->votes = $this->session->currentIssue()->votes->filter(fn(Vote $vote) => $vote->value !== null)->mapWithKeys(
                fn(Vote $vote) => [$vote->user_id => $vote->value],
            )->toArray();
            $this->votes[Auth::id()] = $this->session->currentIssue()->votes()->whereUserId(Auth::id())->first()?->value;
        }
    }
}
