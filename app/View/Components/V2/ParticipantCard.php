<?php

declare(strict_types=1);

namespace App\View\Components\V2;

use App\Enums\SessionParticipantRole;
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
 * - Optional: Badge (z.B. "SKIPPED", "Zuschauer")
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

    public string $badgeTone;

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
        public ?SessionParticipantRole $participantRole = null,
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

        $this->badge = null;
        $this->badgeTone = 'error';

        if ($userIsOwner) {
            $this->avatarBg = 'bg-accent';
            $this->avatarText = 'text-accent-content';
            $this->borderColor = 'border-accent';
            $this->cardBg = 'bg-accent/5';
            $this->icon = 'PO';
        } elseif ($this->participantRole === SessionParticipantRole::Viewer) {
            $this->avatarBg = 'bg-info/20';
            $this->avatarText = 'text-info';
            $this->borderColor = 'border-info/40';
            $this->cardBg = 'bg-info/5';
            $this->icon = 'EYE';

            if (! $votingActive) {
                $this->badge = null;
            } elseif (! $this->votesRevealed) {
                $this->badge = null;
            } elseif (! $hasVoted) {
                $this->badge = 'Zuschauer';
                $this->badgeTone = 'info';
            } else {
                $this->avatarBg = 'bg-success';
                $this->avatarText = 'text-success-content';
                $this->borderColor = 'border-success';
                $this->cardBg = 'bg-success/5';
                $this->icon = (string) $userVote;
                $this->badge = null;
            }
        } elseif (! $votingActive) {
            $this->avatarBg = 'bg-base-300';
            $this->avatarText = 'text-base-content';
            $this->borderColor = 'border-base-300';
            $this->cardBg = 'bg-base-100';
            $this->icon = '?';
        } elseif ($this->votesRevealed && ! $hasVoted) {
            $this->avatarBg = 'bg-error';
            $this->avatarText = 'text-error-content';
            $this->borderColor = 'border-error';
            $this->cardBg = 'bg-error/5';
            $this->icon = '?';
            $this->badge = 'SKIPPED';
        } elseif ($this->votesRevealed && $hasVoted) {
            $this->avatarBg = 'bg-success';
            $this->avatarText = 'text-success-content';
            $this->borderColor = 'border-success';
            $this->cardBg = 'bg-success/5';
            $this->icon = (string) $userVote;
        } elseif ($hasVoted) {
            $this->avatarBg = 'bg-success';
            $this->avatarText = 'text-success-content';
            $this->borderColor = 'border-success';
            $this->cardBg = 'bg-success/5';
            $this->icon = '✓';
        } else {
            $this->avatarBg = 'bg-warning';
            $this->avatarText = 'text-warning-content';
            $this->borderColor = 'border-warning';
            $this->cardBg = 'bg-warning/5';
            $this->icon = '?';
        }

        $this->dotColor = $this->isOnline ? 'bg-success' : 'bg-base-content/30';
    }

    public function render(): View|Closure|string
    {
        return view('components.v2.participant-card');
    }
}
