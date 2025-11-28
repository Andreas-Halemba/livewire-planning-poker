<?php

declare(strict_types=1);

namespace App\Livewire\V2\Traits;

use App\Enums\IssueStatus;
use App\Events\AddVote;
use App\Events\HideVotes;
use App\Events\IssueCanceled;
use App\Events\IssueSelected;
use App\Events\RevealVotes;
use App\Models\Issue;
use App\Models\Vote;
use Illuminate\Support\Facades\Auth;

/**
 * Trait für Voting-Logik.
 *
 * Verwaltet das Voting-System: Start, Reveal, Hide, Cancel, Confirm, Restart.
 */
trait HandlesVoting
{
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

    /**
     * Gibt die Voting Event Listeners zurück.
     *
     * @return array<string, string>
     */
    protected function getVotingListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleAddVote',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'handleRevealVotes',
            "echo-presence:session.{$this->session->invite_code},.HideVotes" => 'handleHideVotes',
        ];
    }

    // ===== Event Handlers =====

    public function handleIssueSelected(): void
    {
        $this->votesRevealed = false;
        $this->loadCurrentIssue();

        // Dispatch JavaScript-Event zum Scrollen zum Voting-Panel
        $this->dispatch('scroll-to-voting-panel');
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

    // ===== Owner Actions =====

    /**
     * Startet das Voting für ein Issue (nur Owner).
     */
    public function startVoting(int $issueId): void
    {
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
        $this->loadVotedUsers();

        broadcast(new IssueSelected($this->session->invite_code))->toOthers();

        // Dispatch JavaScript-Event zum Scrollen zum Voting-Panel
        $this->dispatch('scroll-to-voting-panel');
    }

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

        broadcast(new RevealVotes($this->session->invite_code))->toOthers();
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

        broadcast(new HideVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Bricht das aktuelle Voting ab (nur Owner).
     */
    public function cancelVoting(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        $this->currentIssue->status = IssueStatus::NEW;
        $this->currentIssue->save();

        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];

        broadcast(new IssueCanceled($this->session->invite_code))->toOthers();
    }

    /**
     * Startet das Voting neu - löscht alle Votes (nur Owner).
     */
    public function restartVoting(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        Vote::query()
            ->where('issue_id', $this->currentIssue->id)
            ->delete();

        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];
        $this->myVote = null;

        broadcast(new HideVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Bestätigt die Schätzung und schließt das Issue ab (nur Owner).
     */
    public function confirmEstimate(int $storypoints): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        $this->currentIssue->storypoints = $storypoints;
        $this->currentIssue->status = IssueStatus::FINISHED;
        $this->currentIssue->save();

        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];
        $this->myVote = null;

        broadcast(new IssueCanceled($this->session->invite_code))->toOthers();
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

        broadcast(new AddVote($this->session->invite_code, Auth::user()))->toOthers();
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

        broadcast(new AddVote($this->session->invite_code, Auth::user()))->toOthers();
    }

    // ===== Helper Methods =====

    /**
     * Lädt das aktuelle Issue im Voting-Status.
     */
    protected function loadCurrentIssue(): void
    {
        $this->currentIssue = Issue::query()
            ->where('session_id', $this->session->id)
            ->where('status', 'voting')
            ->first();

        $this->loadVotedUsers();
    }

    /**
     * Lädt alle abgegebenen Votes für das aktuelle Issue.
     */
    protected function loadVotedUsers(): void
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
        $this->myVote = $this->votesByUser[Auth::id()] ?? null;
    }
}
