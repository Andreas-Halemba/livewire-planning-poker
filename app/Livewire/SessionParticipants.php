<?php

namespace App\Livewire;

use App\Enums\IssueStatus;
use App\Events\IssueCanceled;
use App\Models\Session;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

/**
 * Zeigt die Teilnehmer einer Session und deren Voting-Status an.
 * 
 * Props vom Parent (Voting.php):
 * - session: Die aktuelle Session
 * - votesRevealed: Ob Votes angezeigt werden (vom Parent verwaltet)
 */
class SessionParticipants extends Component
{
    use InspectorLivewire;

    public Session $session;

    /** @var bool Vom Parent verwaltet - zeigt an ob Votes sichtbar sind */
    public bool $votesRevealed = false;

    /** @var Collection<int|string, \App\Models\User> */
    public Collection $participants;

    /** @var array<int|string, mixed> */
    public array $votes = [];

    public function mount(): void
    {
        $this->participants = collect([]);
        if (Auth::user()) {
            $this->participants->push(Auth::user());
        }
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            // Presence Channel Events für Teilnehmer-Verwaltung
            "echo-presence:session.{$this->session->invite_code},here" => 'updateUsers',
            "echo-presence:session.{$this->session->invite_code},joining" => 'userJoins',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'userLeaves',
            // Vote Events für Vote-Status Anzeige
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleNewVote',
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueChange',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueChange',
        ];
    }

    public function render(): View
    {
        $this->updateVotesDisplay();
        
        // Sort participants: owner first, then others
        $this->participants = $this->participants->sortBy(function (User $user) {
            return $user->id === $this->session->owner_id ? 0 : 1;
        })->values();

        // Dispatch Livewire event with participants count for parent component
        $this->dispatch('participants-count-updated', count: $this->participants->count());

        return view('livewire.session-participants');
    }

    public function handleIssueChange(): void
    {
        $this->votes = [];
        $this->updateVotesDisplay();
    }

    public function handleNewVote(User $user): void
    {
        $this->votes[$user->id] = 'X';
    }

    /**
     * User joins the session.
     * @param array<int|string, mixed> $user
     */
    public function userJoins(array $user): void
    {
        if (!isset($user['id']) || !$user['id']) {
            return;
        }

        if ($user['id'] === Auth::id() || $this->participants->contains('id', $user['id'])) {
            return;
        }

        try {
            $foundUser = User::whereId($user['id'])->first();
            if ($foundUser) {
                $this->participants->push($foundUser);
                $this->dispatch('participants-count-updated', count: $this->participants->count());
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to add user {$user['id']} to participants: " . $e->getMessage());
        }
    }

    public function userLeaves(array|User $userData): void
    {
        $userId = is_array($userData) ? ($userData['id'] ?? null) : ($userData->id ?? null);

        if (!$userId) {
            return;
        }

        $this->participants = $this->participants->filter(fn(User $participant) => $participant->id !== $userId);
        $this->dispatch('participants-count-updated', count: $this->participants->count());

        // If the owner leaves, cancel the current voting
        if ($userId === $this->session->owner_id) {
            $currentIssue = $this->session->currentIssue();
            if ($currentIssue && $currentIssue->status === IssueStatus::VOTING) {
                $currentIssue->status = IssueStatus::NEW;
                $currentIssue->save();
                broadcast(new IssueCanceled($this->session->invite_code));
            }
        }
    }

    /** @param array<int|string, mixed> $users */
    public function updateUsers(array $users): void
    {
        try {
            $this->participants = collect($users)
                ->filter(fn(array $user) => isset($user['id']))
                ->map(function (array $user): ?User {
                    try {
                        return User::whereId($user['id'])->first();
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to load user {$user['id']}: " . $e->getMessage());
                        return null;
                    }
                })
                ->filter();

            $this->dispatch('participants-count-updated', count: $this->participants->count());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to update users: " . $e->getMessage());
        }
    }

    public function userDidVote(string $id): bool
    {
        return Arr::has($this->votes, $id);
    }

    private function updateVotesDisplay(): void
    {
        $currentIssue = $this->session->currentIssue();
        if (!$currentIssue) {
            $this->votes = [];
            return;
        }

        $currentIssue->load('votes');

        if ($this->votesRevealed) {
            // Show actual vote values
            $this->votes = $currentIssue->votes->mapWithKeys(
                fn(Vote $vote) => [$vote->user_id => $vote->value],
            )->toArray();
        } else {
            // Show 'X' to indicate vote exists (value hidden)
            $this->votes = $currentIssue->votes->mapWithKeys(
                fn(Vote $vote) => [$vote->user_id => 'X'],
            )->toArray();
        }
    }
}
