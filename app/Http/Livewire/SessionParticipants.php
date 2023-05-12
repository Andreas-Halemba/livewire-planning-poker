<?php

namespace App\Http\Livewire;

use App\Events\RevealVotes;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SessionParticipants extends Component
{
    public Session $session;

    public Collection $participants;

    public array $votes = [];

    public array $participantsVoted = [];

    public ?Issue $issue;

    public bool $votesRevealed = false;

    public function mount(): void
    {
        $this->participants = collect([]);
    }

    public function render(): View
    {
        $this->issue = Issue::whereStatus(Issue::STATUS_VOTING)->whereSessionId($this->session->id)->first();
        if ($this->issue) {
            $this->participantsVoted = $this->issue->votes()->pluck('user_id', 'user_id')->toArray();
            $this->votes[auth()->id()] = $this->issue->votes()->whereUserId(auth()->id())->first()?->value;
        }
        return view('livewire.session-participants');
    }

    public function getListeners(): array
    {
        return [
            'voteIssue' => 'setCurrentVote',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'revealVotes',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'newVote',
            "echo-presence:session.{$this->session->invite_code},here" => 'updateUsers',
            "echo-presence:session.{$this->session->invite_code},joining" => 'userJoins',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'userLeaves',
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'reload',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'reload',
        ];
    }

    public function reload(): void
    {
        $this->issue = null;
        $this->render();
    }

    public function userJoins(User $user): void
    {
        if ($user->id === Auth::id() || $this->participants->contains('id', $user->id)) {
            return;
        }
        $user = User::find($user['id']);
        if ($user instanceof User) {
            $this
                ->participants
                ->push($user->toArray());
        }
    }

    public function userLeaves(User $user): void
    {
        $this->participants = $this->participants->filter(fn ($participant) => $participant['id'] !== $user->id);
    }

    public function updateUsers(array $users): void
    {
        $participants = new Collection(Arr::map($users, function (array $user) {
            $user = User::find($user['id']);

            return $user instanceof User ? $user->toArray() : null;
        }));
        $participants = $participants->filter(fn (array $user) => $user !== null);

        $this->participants = $participants;
    }

    public function setCurrentVote(int $vote): void
    {
        $this->votes[auth()->id()] = $vote;
    }

    public function revealVotes(): void
    {
        $this->votes = Issue::whereStatus(Issue::STATUS_VOTING)->whereSessionId($this->session->id)->first()?->votes()->pluck('value', 'user_id')->toArray() ?? [];
        $this->votesRevealed = true;
    }

    public function newVote(User $user): void
    {
        $this->participantsVoted[$user->id] = $user->id;
    }

    public function sendRevealEvent(): void
    {
        broadcast(new RevealVotes($this->session));
    }

    public function userDidVote(string $id): bool
    {
        return Arr::has($this->participantsVoted, $id);
    }
}
