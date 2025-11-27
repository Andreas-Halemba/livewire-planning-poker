<?php

declare(strict_types=1);

namespace App\Livewire\V2;

use App\Enums\IssueStatus;
use App\Events\IssueSelected;
use App\Models\Issue;
use App\Models\Session;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * V2 SessionPage - Schritt 2c: Mit Voting-Status.
 *
 * Zeigt alle Session-Mitglieder mit:
 * - Präsenz-Status (online/offline via Presence Channel)
 * - Voting-Status (wartet/abgestimmt/übersprungen)
 */
class SessionPage extends Component
{
    public Session $session;

    /** @var array<int> IDs der online User */
    public array $onlineUserIds = [];

    /** @var Issue|null Aktuell zu schätzendes Issue */
    public ?Issue $currentIssue = null;

    /** @var bool Wurden die Votes aufgedeckt? */
    public bool $votesRevealed = false;

    /** @var array<int> User-IDs die abgestimmt haben */
    public array $votedUserIds = [];

    /** @var array<int, string> Vote-Werte pro User-ID */
    public array $votesByUser = [];

    /** @var array<int> Verfügbare Voting-Karten */
    public array $cards = [1, 2, 3, 5, 8, 13, 21, 100];

    /** @var int|null Eigene Auswahl des aktuellen Users */
    public ?int $myVote = null;

    public function mount(string $inviteCode): void
    {
        $this->session = Session::with(['issues', 'users', 'owner'])
            ->where('invite_code', $inviteCode)
            ->firstOrFail();

        // Aktueller User ist immer online
        if (Auth::check()) {
            $this->onlineUserIds = [Auth::id()];
        }

        // Initiales Laden des aktuellen Issues
        $this->loadCurrentIssue();
    }

    /**
     * Event Listeners für Presence + Voting Events.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            // Presence Events
            "echo-presence:session.{$this->session->invite_code},here" => 'handleUsersHere',
            "echo-presence:session.{$this->session->invite_code},joining" => 'handleUserJoining',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'handleUserLeaving',
            // Voting Events
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleAddVote',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'handleRevealVotes',
            "echo-presence:session.{$this->session->invite_code},.HideVotes" => 'handleHideVotes',
            // Issue Events
            "echo-presence:session.{$this->session->invite_code},.IssueOrderChanged" => 'handleIssueOrderChanged',
        ];
    }

    // ===== Presence Handlers =====

    /**
     * @param array<int, array{id: int}> $users
     */
    public function handleUsersHere(array $users): void
    {
        $this->onlineUserIds = collect($users)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param array{id: int} $user
     */
    public function handleUserJoining(array $user): void
    {
        if (isset($user['id']) && !in_array($user['id'], $this->onlineUserIds)) {
            $this->onlineUserIds[] = $user['id'];
        }
    }

    /**
     * @param array{id: int} $user
     */
    public function handleUserLeaving(array $user): void
    {
        if (isset($user['id'])) {
            $this->onlineUserIds = array_values(
                array_filter($this->onlineUserIds, fn($id) => $id !== $user['id']),
            );
        }
    }

    // ===== Voting Handlers =====

    public function handleIssueSelected(): void
    {
        $this->votesRevealed = false;
        $this->loadCurrentIssue();
    }

    public function handleIssueCanceled(): void
    {
        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
    }

    public function handleAddVote(): void
    {
        $this->loadVotedUsers();
    }

    public function handleRevealVotes(): void
    {
        $this->votesRevealed = true;
        $this->loadVotedUsers();
    }

    public function handleHideVotes(): void
    {
        $this->votesRevealed = false;
        $this->loadVotedUsers();
    }

    public function handleIssueOrderChanged(): void
    {
        // Session Issues neu laden, um neue Reihenfolge zu erhalten
        $this->session->load('issues');
    }

    // ===== Owner Actions =====

    /**
     * Deckt die Votes auf (nur Owner).
     */
    public function revealVotes(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        $this->votesRevealed = true;
        $this->loadVotedUsers();

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\RevealVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Verdeckt die Votes wieder (nur Owner).
     */
    public function hideVotes(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        $this->votesRevealed = false;

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\HideVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Bricht das aktuelle Voting ab (nur Owner).
     */
    public function cancelVoting(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        // Issue zurück auf "new" setzen
        $this->currentIssue->status = IssueStatus::NEW;
        $this->currentIssue->save();

        // State zurücksetzen
        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\IssueCanceled($this->session->invite_code))->toOthers();
    }

    /**
     * Startet das Voting neu - löscht alle Votes (nur Owner).
     */
    public function restartVoting(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        // Alle Votes für dieses Issue löschen
        Vote::query()
            ->where('issue_id', $this->currentIssue->id)
            ->delete();

        // State zurücksetzen
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];
        $this->myVote = null;

        // Event broadcasten (HideVotes signalisiert "neu starten")
        broadcast(new \App\Events\HideVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Bestätigt die Schätzung und schließt das Issue ab (nur Owner).
     */
    public function confirmEstimate(int $storypoints): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        // Storypoints setzen und Issue abschließen
        $this->currentIssue->storypoints = $storypoints;
        $this->currentIssue->status = IssueStatus::FINISHED;
        $this->currentIssue->save();

        // State zurücksetzen
        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];
        $this->myVote = null;

        // Event an andere Teilnehmer broadcasten (Voting beendet)
        broadcast(new \App\Events\IssueCanceled($this->session->invite_code))->toOthers();
    }

    /**
     * Startet das Voting für ein Issue (nur Owner).
     */
    public function startVoting(int $issueId): void
    {
        // Nur Owner darf Voting starten
        if (Auth::id() !== $this->session->owner_id) {
            return;
        }

        // Alle laufenden Votings beenden
        Issue::query()
            ->where('session_id', $this->session->id)
            ->where('status', IssueStatus::VOTING)
            ->update(['status' => IssueStatus::NEW]);

        // Neues Voting starten
        $issue = Issue::find($issueId);
        if (!$issue || $issue->session_id !== $this->session->id) {
            return;
        }

        $issue->status = IssueStatus::VOTING;
        $issue->save();

        // State aktualisieren
        $this->currentIssue = $issue;
        $this->votesRevealed = false;
        $this->loadVotedUsers(); // Lädt existierende async Votes aus der DB

        // Event an andere Teilnehmer broadcasten
        broadcast(new IssueSelected($this->session->invite_code))->toOthers();
    }

    // ===== Voter Actions =====

    /**
     * Gibt einen Vote ab (für Teilnehmer).
     */
    public function submitVote(int $card): void
    {
        if (!$this->currentIssue || !Auth::check()) {
            return;
        }

        Vote::query()->updateOrCreate([
            'user_id' => Auth::id(),
            'issue_id' => $this->currentIssue->id,
        ], [
            'value' => $card,
        ]);

        $this->myVote = $card;
        $this->loadVotedUsers();

        // Event broadcasten
        broadcast(new \App\Events\AddVote($this->session->invite_code, Auth::user()))->toOthers();
    }

    /**
     * Entfernt den eigenen Vote (für Teilnehmer).
     */
    public function removeVote(): void
    {
        if (!$this->currentIssue || !Auth::check()) {
            return;
        }

        Vote::query()
            ->where('user_id', Auth::id())
            ->where('issue_id', $this->currentIssue->id)
            ->delete();

        $this->myVote = null;
        $this->loadVotedUsers();

        // Event broadcasten
        broadcast(new \App\Events\AddVote($this->session->invite_code, Auth::user()))->toOthers();
    }

    // ===== Helper Methods =====

    private function loadCurrentIssue(): void
    {
        $this->currentIssue = Issue::query()
            ->where('session_id', $this->session->id)
            ->where('status', 'voting')
            ->first();

        $this->loadVotedUsers();
    }

    private function loadVotedUsers(): void
    {
        if (!$this->currentIssue) {
            $this->votedUserIds = [];
            $this->votesByUser = [];
            $this->myVote = null;

            return;
        }

        $votes = Vote::query()
            ->where('issue_id', $this->currentIssue->id)
            ->get(['user_id', 'value']);

        $this->votedUserIds = $votes->pluck('user_id')->all();
        $this->votesByUser = $votes->pluck('value', 'user_id')->all();

        // Eigenen Vote laden
        $this->myVote = $this->votesByUser[Auth::id()] ?? null;
    }

    /**
     * Aktualisiert die Reihenfolge der Issues (nur Owner).
     *
     * @param array<int> $orderedIds Array von Issue-IDs in neuer Reihenfolge
     */
    public function updateIssueOrder(array $orderedIds): void
    {
        if (Auth::id() !== $this->session->owner_id) {
            return;
        }

        foreach ($orderedIds as $position => $issueId) {
            Issue::query()
                ->where('id', $issueId)
                ->where('session_id', $this->session->id)
                ->update(['position' => $position]);
        }

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\IssueOrderChanged($this->session->invite_code))->toOthers();
    }

    public function render(): View
    {
        // Issues neu laden für aktuelle Daten
        $this->session->load('issues');

        // Issues gruppieren und nach Position sortieren
        $openIssues = $this->session->issues
            ->where('status', '!=', 'finished')
            ->where('status', '!=', 'voting')
            ->sortBy('position');

        $finishedIssues = $this->session->issues
            ->where('status', 'finished')
            ->sortBy('position');

        return view('livewire.v2.session-page', [
            'isOwner' => Auth::id() === $this->session->owner_id,
            'issueCount' => $this->session->issues->count(),
            'finishedCount' => $finishedIssues->count(),
            'participantCount' => $this->session->users->count(),
            'onlineCount' => count($this->onlineUserIds),
            'openIssues' => $openIssues,
            'finishedIssues' => $finishedIssues,
        ]);
    }
}
