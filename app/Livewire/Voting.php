<?php

namespace App\Livewire;

use App\Models\Issue;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;

/**
 * Voting - Parent-Komponente für die Planning Poker Session.
 * 
 * Diese Komponente verwaltet den zentralen State und gibt ihn als Props an Children weiter:
 * - votesRevealed: Ob die Votes angezeigt werden sollen
 * - groupedVotes: Gruppierte Vote-Ergebnisse
 * - currentIssue: Das aktuell zu schätzende Issue
 * 
 * KOMPONENTEN-HIERARCHIE:
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │ Voting (Parent - Single Source of Truth)                           │
 * │ ├── SessionParticipants ← Props: votesRevealed                     │
 * │ ├── VotingCards ← Props: votesRevealed, groupedVotes (nur Voter)   │
 * │ ├── Voting/Voter ← Props: session (nur Voter)                      │
 * │ └── Voting/Owner ← Props: votesRevealed, groupedVotes (nur Owner)  │
 * │     └── JiraImport ← Props: session                                │
 * └─────────────────────────────────────────────────────────────────────┘
 * 
 * ECHO EVENTS (Broadcasting):
 * - IssueSelected, IssueCanceled → Reset votesRevealed
 * - RevealVotes → votesRevealed = true
 * - HideVotes → votesRevealed = false
 * - AddVote → Reload votes data
 * 
 * LIVEWIRE EVENTS (intern):
 * - participants-count-updated → Update Teilnehmer-Zähler
 */
class Voting extends Component
{
    use InspectorLivewire;

    public Session $session;

    public string $inviteCode;

    public int $participantsCount = 0;

    /** @var bool Zentraler State für Votes-Anzeige - wird an Children weitergegeben */
    public bool $votesRevealed = false;

    public function mount(string $inviteCode): RedirectResponse|LivewireRedirector|null
    {
        $this->inviteCode = $inviteCode;
        $this->session = Session::with(['issues.votes.user', 'users'])->whereInviteCode($this->inviteCode)->firstOrFail();

        if ($this->session->archived_at !== null) {
            return Redirect::route('session.archived', $inviteCode);
        }
        // Initialize with database count as fallback until SessionParticipants component updates it
        $this->participantsCount = $this->session->users->count();
        if (Auth::hasUser()) {
            $this->attachUserToSession();
        }
        return null;
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'handleRevealVotes',
            "echo-presence:session.{$this->session->invite_code},.HideVotes" => 'handleHideVotes',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleAddVote',
            'participants-count-updated' => 'updateParticipantsCount',
        ];
    }

    public function handleIssueSelected(): void
    {
        $this->votesRevealed = false;
        $this->session->load(['issues.votes.user']);
    }

    public function handleIssueCanceled(): void
    {
        $this->votesRevealed = false;
        $this->session->load(['issues.votes.user']);
    }

    public function handleRevealVotes(): void
    {
        $this->votesRevealed = true;
        // Reload votes to get fresh data
        $this->session->load(['issues.votes.user']);
    }

    public function handleHideVotes(): void
    {
        $this->votesRevealed = false;
    }

    public function handleAddVote(): void
    {
        // Reload votes to get fresh data
        $this->session->load(['issues.votes.user']);
    }

    public function updateParticipantsCount(int $count): void
    {
        $this->participantsCount = $count;
        $this->skipRender();
    }

    /**
     * Zentrales currentIssue - wird gecached und an Children weitergegeben.
     */
    #[Computed]
    public function currentIssue(): ?Issue
    {
        return $this->session->currentIssue();
    }

    /**
     * Zentrales groupedVotes - wird nur berechnet wenn nötig.
     * @return array<string, array{count: int, participants: array<string>}>
     */
    #[Computed]
    public function groupedVotes(): array
    {
        $currentIssue = $this->currentIssue;
        if (!$currentIssue || !$this->votesRevealed) {
            return [];
        }

        $groupedVotes = [];
        $votes = $currentIssue->votes()->with('user')->get();

        foreach ($votes as $vote) {
            if ($vote->value !== null) {
                $value = (string) $vote->value;
                if (!isset($groupedVotes[$value])) {
                    $groupedVotes[$value] = [
                        'count' => 0,
                        'participants' => [],
                    ];
                }
                $groupedVotes[$value]['count']++;
                $groupedVotes[$value]['participants'][] = $vote->user->name;
            }
        }

        ksort($groupedVotes, SORT_NUMERIC);

        return $groupedVotes;
    }

    public function render(): View
    {
        if (!$this->session->relationLoaded('issues')) {
            $this->session->load(['issues.votes.user']);
        }

        return view('livewire.voting', [
            'currentIssue' => $this->currentIssue,
            'groupedVotes' => $this->groupedVotes,
        ]);
    }

    private function attachUserToSession(): void
    {
        if (blank(Auth::user())) {
            return;
        }
        if ($this->session->users->contains(Auth::user())) {
            return;
        }
        $this->session->users()->attach(Auth::user());
        $this->session->load('users');
    }
}
