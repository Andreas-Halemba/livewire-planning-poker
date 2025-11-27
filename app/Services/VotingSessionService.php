<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\Session;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Collection;

/**
 * Zentrale Business-Logik für Voting-Sessions.
 *
 * Dieser Service kapselt alle Datenbank-Abfragen und Berechnungen,
 * die von mehreren Livewire-Komponenten benötigt werden.
 */
class VotingSessionService
{
    /**
     * Holt das aktuell zu schätzende Issue (Status: VOTING).
     */
    public function getCurrentIssue(Session $session): ?Issue
    {
        return Issue::query()
            ->where('session_id', $session->id)
            ->where('status', IssueStatus::VOTING)
            ->first();
    }

    /**
     * Holt Issues nach Status gruppiert.
     *
     * @return array{open: Collection<int, Issue>, voting: ?Issue, finished: Collection<int, Issue>}
     */
    public function getIssuesByStatus(Session $session): array
    {
        $issues = Issue::query()
            ->where('session_id', $session->id)
            ->orderBy('created_at')
            ->get();

        return [
            'open' => $issues->filter(fn (Issue $i) => $i->status === IssueStatus::NEW),
            'voting' => $issues->first(fn (Issue $i) => $i->status === IssueStatus::VOTING),
            'finished' => $issues->filter(fn (Issue $i) => $i->status === IssueStatus::FINISHED),
        ];
    }

    /**
     * Berechnet gruppierte Votes mit Teilnehmernamen.
     *
     * @return array<string, array{count: int, participants: array<string>}>
     */
    public function getGroupedVotes(Issue $issue): array
    {
        $votes = $issue->votes()->with('user')->get();
        $grouped = [];

        foreach ($votes as $vote) {
            if ($vote->value === null) {
                continue; // Skip "?" votes in grouping
            }

            $value = (string) $vote->value;
            if (!isset($grouped[$value])) {
                $grouped[$value] = [
                    'count' => 0,
                    'participants' => [],
                ];
            }
            $grouped[$value]['count']++;
            $grouped[$value]['participants'][] = $vote->user->name;
        }

        ksort($grouped, SORT_NUMERIC);

        return $grouped;
    }

    /**
     * Prüft, ob ein User bereits für ein Issue abgestimmt hat.
     */
    public function hasUserVoted(Issue $issue, User $user): bool
    {
        return Vote::query()
            ->where('issue_id', $issue->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Holt den Vote-Wert eines Users für ein Issue.
     */
    public function getUserVote(Issue $issue, User $user): ?Vote
    {
        return Vote::query()
            ->where('issue_id', $issue->id)
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Holt Vote-Status für alle Teilnehmer eines Issues.
     *
     * @return array<int, array{user_id: int, has_voted: bool, value: int|null}>
     */
    public function getVoteStatusForParticipants(Issue $issue, Collection $participants): array
    {
        $votes = Vote::query()
            ->where('issue_id', $issue->id)
            ->whereIn('user_id', $participants->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return $participants->map(function (User $user) use ($votes) {
            $vote = $votes->get($user->id);

            return [
                'user_id' => $user->id,
                'has_voted' => $vote !== null,
                'value' => $vote?->value,
            ];
        })->all();
    }

    /**
     * Zählt wie viele Votes für ein Issue abgegeben wurden.
     */
    public function getVoteCount(Issue $issue): int
    {
        return Vote::query()
            ->where('issue_id', $issue->id)
            ->count();
    }

    /**
     * Zählt Votes ohne den Owner.
     */
    public function getVoteCountExcludingOwner(Issue $issue, int $ownerId): int
    {
        return Vote::query()
            ->where('issue_id', $issue->id)
            ->where('user_id', '!=', $ownerId)
            ->count();
    }

    /**
     * Holt Issues, für die der User noch nicht abgestimmt hat (für async voting).
     *
     * @return Collection<int, Issue>
     */
    public function getUnvotedIssuesForUser(Session $session, User $user): Collection
    {
        $votedIssueIds = Vote::query()
            ->where('user_id', $user->id)
            ->whereHas('issue', fn ($q) => $q->where('session_id', $session->id))
            ->pluck('issue_id');

        return Issue::query()
            ->where('session_id', $session->id)
            ->where('status', '!=', IssueStatus::FINISHED)
            ->where('status', '!=', IssueStatus::VOTING)
            ->whereNotIn('id', $votedIssueIds)
            ->get();
    }

    /**
     * Holt Issues, für die der User bereits asynchron abgestimmt hat.
     *
     * @return Collection<int, Issue>
     */
    public function getAsyncVotedIssuesForUser(Session $session, User $user): Collection
    {
        return Issue::query()
            ->where('session_id', $session->id)
            ->where('status', '!=', IssueStatus::FINISHED)
            ->where('status', '!=', IssueStatus::VOTING)
            ->whereHas('votes', fn ($q) => $q->where('user_id', $user->id))
            ->with(['votes' => fn ($q) => $q->where('user_id', $user->id)])
            ->get();
    }

    /**
     * Berechnet Statistiken für die Session.
     *
     * @return array{total: int, open: int, finished: int, total_story_points: int}
     */
    public function getSessionStatistics(Session $session): array
    {
        $issues = Issue::query()
            ->where('session_id', $session->id)
            ->get();

        return [
            'total' => $issues->count(),
            'open' => $issues->filter(fn (Issue $i) => $i->status !== IssueStatus::FINISHED)->count(),
            'finished' => $issues->filter(fn (Issue $i) => $i->status === IssueStatus::FINISHED)->count(),
            'total_story_points' => $issues->sum('storypoints') ?? 0,
        ];
    }
}

