<?php

namespace App\Http\Livewire;

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

    public Collection $participants;

    public ?Issue $issue;

    public array $votes = [];

    public array $participantsVoted = [];

    public bool $votesRevealed = false;

    public function mount(): void
    {
        $this->participants = collect([User::query()->find(Auth::id())?->toArray()]);
    }

    public function render(): View
    {
        $this->updateIssueData();
        return view('livewire.session-participants');
    }

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

    public function userJoins(User $user): void
    {
        if ($user->id === Auth::id() || $this->participants->contains('id', $user->id)) {
            return;
        }
        $this->participants->push(User::query()->findOrFail($user['id'])->toArray());
    }

    public function userLeaves(User $user): void
    {
        $this->participants = $this->participants->filter(fn ($participant) => $participant['id'] !== $user->id);
        $this->participants = $this->participants->filter(fn ($participant) => $participant['id'] !== $user->id);
    }

    public function updateUsers(array $users): void
    {
        $this->participants = collect(
            Arr::map($users, fn ($user) => User::whereId($user['id'])->firstOrFail()->toArray())
        );
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
        $this->issue = $this->session->currentIssue();
        if ($this->issue) {
            $this->votes = $this->issue->votes->filter(fn (Vote $vote) => $vote->value !== null)->mapWithKeys(
                fn (Vote $vote) => [$vote->user_id => $vote->value]
            )->toArray();
            $this->votes[auth()->id()] = $this->issue->votes()->whereUserId(auth()->id())->first()?->value;
        }
    }
}
