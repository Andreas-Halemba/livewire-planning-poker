<?php

namespace App\Http\Livewire;

use App\Events\RevealVotes;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
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

    public function mount()
    {
        $this->issue = Issue::whereStatus(Issue::STATUS_VOTING)->whereSessionId($this->session->id)->first();
        $this->participants = collect([Auth::user()->toArray()]);
        if ($this->issue) {
            $this->participantsVoted = $this->issue->votes()->pluck('user_id', 'user_id')->toArray();
            $this->votes[auth()->id()] = $this->issue?->votes()->whereUserId(auth()->id())->first()?->value;
        }
    }

    public function render()
    {
        $this->participants->sortBy('id');

        return view('livewire.session-participants');
    }

    public function getListeners()
    {
        return [
            'voteIssue' => 'setCurrentVote',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'revealVotes',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'newVote',
            "echo-presence:session.{$this->session->invite_code},here" => 'updateUsers',
            "echo-presence:session.{$this->session->invite_code},joining" => 'userJoins',
        ];
    }

    public function userJoins(User $user): void
    {
        if ($user->id === Auth::id() || $this->participants->contains('id', $user->id)) {
            return;
        }
        $this->participants->push($user->toArray());
    }

    public function updateUsers(array $users): void
    {
        $this->participants = collect($users);
    }

    public function setCurrentVote($vote): void
    {
        $this->votes[auth()->id()] = $vote;
    }

    public function revealVotes(): void
    {
        $this->votes = Issue::whereStatus(Issue::STATUS_VOTING)->whereSessionId($this->session->id)->first()?->votes()->pluck('value', 'user_id')->toArray();
        $this->votesRevealed = true;
    }

    public function newVote(User $user)
    {
        $this->participantsVoted[$user->id] = $user->id;
    }

    public function sendRevealEvent()
    {
        broadcast(new RevealVotes($this->session));
    }

    public function userDidVote($id): bool
    {
        ray(Arr::has($this->participantsVoted, $id));

        return Arr::has($this->participantsVoted, $id);
    }
}
