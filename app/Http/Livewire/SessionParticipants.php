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

    public array $votes = [];

    public array $participantsVoted = [];

    public ?Issue $issue;

    public bool $votesRevealed = false;

    public function mount(): void
    {
        /** @phpstan-ignore-next-line */
        $this->participants = collect([Auth::getUser()->toArray()]);
    }

    public function render(): View
    {
        $this->issue = Issue::whereStatus(Issue::STATUS_VOTING)->whereSessionId($this->session->id)->first();
        if ($this->issue) {
            $this->participantsVoted = $this->issue->votes()->pluck('user_id', 'user_id')->toArray();
            $this->votes[auth()->id()] = $this->issue->votes()->whereUserId(auth()->id())->first()?->value;
            $this->votes[auth()->id()] = $this->issue->votes()->whereUserId(auth()->id())->first()?->value;
        }
        return view('livewire.session-participants');
    }

    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'revealVotes',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'newVote',
            "echo-presence:session.{$this->session->invite_code},here" => 'updateUsers',
            "echo-presence:session.{$this->session->invite_code},joining" => 'userJoins',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'userLeaves',
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => '$refresh',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => '$refresh',
        ];
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
        $currentVotingIssue = Issue::query()
            ->whereBelongsTo($this->session)
            ->whereStatus(Issue::STATUS_VOTING)
            ->first();
        if (! $currentVotingIssue) {
            return;
        }
        $this->votes = Vote::query()->whereBelongsTo($currentVotingIssue)->get()->pluck('value', 'user_id')->toArray();
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

    public function userDidVote(int $id): bool
    {
        return Arr::has($this->participantsVoted, (string) $id);
    }
}
