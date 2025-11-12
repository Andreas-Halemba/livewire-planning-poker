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
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class SessionParticipants extends Component
{
    use InspectorLivewire;

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
        $this->issue = null;
        $this->votes = [];
        $this->participantsVoted = [];
        $this->votesRevealed = false;

        if (Auth::user()) {
            $this->participants->push(Auth::user());
        }
    }

    public function render(): View
    {
        $this->updateIssueData();
        // Sort participants: owner first, then others
        $this->participants = $this->participants->sortBy(function (User $user) {
            return $user->id === $this->session->owner_id ? 0 : 1;
        })->values();

        // Dispatch Livewire event with participants count for parent component
        $this->dispatch('participants-count-updated', count: $this->participants->count());

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
        $this->updateIssueData();
        $this->skipRender();
    }

    public function unsetCurrentIssue(): void
    {
        $this->issue = null;
        $this->reset('votes', 'votesRevealed');
        $this->updateIssueData();
        $this->skipRender();
    }


    /**
     * User joins the session.
     * @param array<int|string, mixed> $user
     */
    public function userJoins(array $user): void
    {
        // Skip if no valid user ID
        if (!isset($user['id']) || !$user['id']) {
            return;
        }

        // Skip if user is current user or already in participants
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
        // Handle both array (from Presence Channel) and User object formats
        $userId = is_array($userData) ? ($userData['id'] ?? null) : ($userData->id ?? null);

        // Skip if no valid user ID
        if (!$userId) {
            return;
        }

        // Remove user from participants list
        $this->participants = $this->participants->filter(fn(User $participant) => $participant->id !== $userId);

        // Dispatch updated count
        $this->dispatch('participants-count-updated', count: $this->participants->count());

        // If the owner leaves, cancel the current voting
        if ($userId === $this->session->owner_id) {
            $currentIssue = $this->session->currentIssue();
            if ($currentIssue && $currentIssue->status === Issue::STATUS_VOTING) {
                $currentIssue->status = Issue::STATUS_NEW;
                $currentIssue->save();
                broadcast(new \App\Events\IssueCanceled($currentIssue))->toOthers();
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

    public function revealVotes(): void
    {
        $currentIssue = $this->session->currentIssue();
        if ($currentIssue) {
            // Use fresh query to get latest votes
            $this->votes = Vote::query()->whereBelongsTo($currentIssue)->get()->pluck('value', 'user_id')->toArray();
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
        // Only skip render if we're not revealing votes
        // This ensures the UI updates to show the vote indicator
    }

    public function sendRevealEvent(): void
    {
        $this->revealVotes();
        broadcast(new RevealVotes($this->session))->toOthers();
    }

    public function userDidVote(string $id): bool
    {
        // User has voted if there's an entry in votes array (value can be null for "?" vote)
        return Arr::has($this->votes, $id);
    }

    private function updateIssueData(): void
    {
        $currentIssue = $this->session->currentIssue();
        if ($currentIssue) {
            // Reload votes relation to get fresh data (important when user votes themselves)
            $currentIssue->load('votes');

            // If votes are revealed, show actual values (including null for "?")
            if ($this->votesRevealed) {
                $this->votes = $currentIssue->votes->mapWithKeys(
                    fn(Vote $vote) => [$vote->user_id => $vote->value],
                )->toArray();
            } else {
                // Otherwise, just mark that users have voted (without showing values)
                // Include ALL votes, even those with null value (for "?" vote)
                $this->votes = $currentIssue->votes->mapWithKeys(
                    fn(Vote $vote) => [$vote->user_id => 'X'], // 'X' indicates vote exists (value hidden)
                )->toArray();
            }

            // Current user can always see their own vote value
            $currentUserVote = $currentIssue->votes()->whereUserId(Auth::id())->first();
            if ($currentUserVote) {
                if ($this->votesRevealed) {
                    $this->votes[Auth::id()] = $currentUserVote->value;
                } else {
                    // Still show 'X' for current user if votes not revealed
                    $this->votes[Auth::id()] = 'X';
                }
            }
        } else {
            // No current issue - clear votes
            $this->votes = [];
        }
    }
}
