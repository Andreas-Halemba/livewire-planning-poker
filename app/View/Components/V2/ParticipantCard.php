<?php

declare(strict_types=1);

namespace App\View\Components\V2;

use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

/**
 * Participant Card Component für V2 Session Page.
 *
 * Zeigt einen Teilnehmer mit:
 * - Online-Status (grüner/grauer Dot)
 * - Voting-Status (Avatar-Farbe + Icon)
 * - Optional: Badge (z.B. "SKIPPED")
 */
class ParticipantCard extends Component
{
    // Computed Properties für das Template
    public string $avatarBg;
    public string $avatarText;
    public string $borderColor;
    public string $cardBg;
    public string $dotColor;
    public string $icon;
    public ?string $badge;
    public bool $isCurrentUser;
    public bool $isOnline;

    public function __construct(
        public User $user,
        public Session $session,
        public ?Issue $currentIssue,
        public array $onlineUserIds,
        public array $votedUserIds,
        public array $votesByUser,
        public bool $votesRevealed,
    ) {
        $this->calculateState();
    }

    /**
     * Berechnet alle visuellen Zustände basierend auf den Eingabe-Props.
     */
    private function calculateState(): void
    {
        $this->isCurrentUser = $this->user->id === Auth::id();
        $this->isOnline = in_array($this->user->id, $this->onlineUserIds);

        $userIsOwner = $this->user->id === $this->session->owner_id;
        $hasVoted = in_array($this->user->id, $this->votedUserIds);
        $userVote = $this->votesByUser[$this->user->id] ?? null;
        $votingActive = $this->currentIssue !== null;

        // Default
        $this->badge = null;

        if ($userIsOwner) {
            // Owner (Product Owner)
            $this->avatarBg = 'bg-accent';
            $this->avatarText = 'text-accent-content';
            $this->borderColor = 'border-accent';
            $this->cardBg = 'bg-accent/5';
            $this->icon = 'PO';
        } elseif (!$votingActive) {
            // Kein Voting aktiv
            $this->avatarBg = 'bg-base-300';
            $this->avatarText = 'text-base-content';
            $this->borderColor = 'border-base-300';
            $this->cardBg = 'bg-base-100';
            $this->icon = '?';
        } elseif ($this->votesRevealed && !$hasVoted) {
            // Votes aufgedeckt, User hat nicht gevoted
            $this->avatarBg = 'bg-error';
            $this->avatarText = 'text-error-content';
            $this->borderColor = 'border-error';
            $this->cardBg = 'bg-error/5';
            $this->icon = '?';
            $this->badge = 'SKIPPED';
        } elseif ($this->votesRevealed && $hasVoted) {
            // Votes aufgedeckt, User hat gevoted → Zeige Vote-Wert
            $this->avatarBg = 'bg-success';
            $this->avatarText = 'text-success-content';
            $this->borderColor = 'border-success';
            $this->cardBg = 'bg-success/5';
            $this->icon = (string) $userVote;
        } elseif ($hasVoted) {
            // Voting läuft, User hat bereits gevoted
            $this->avatarBg = 'bg-success';
            $this->avatarText = 'text-success-content';
            $this->borderColor = 'border-success';
            $this->cardBg = 'bg-success/5';
            $this->icon = '✓';
        } else {
            // Voting läuft, User hat noch nicht gevoted
            $this->avatarBg = 'bg-warning';
            $this->avatarText = 'text-warning-content';
            $this->borderColor = 'border-warning';
            $this->cardBg = 'bg-warning/5';
            $this->icon = '?';
        }

        // Online-Status Dot
        $this->dotColor = $this->isOnline ? 'bg-success' : 'bg-base-content/30';
    }

    public function render(): View|Closure|string
    {
        return view('components.v2.participant-card');
    }
}
