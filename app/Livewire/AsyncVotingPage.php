<?php

namespace App\Livewire;

use App\Enums\IssueStatus;
use App\Events\AsyncVoteUpdated;
use App\Models\Issue;
use App\Models\Session;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;

class AsyncVotingPage extends Component
{
    public Session $session;

    public string $inviteCode;

    /**
     * @var array<int, array<int, array{id:int, name:string}>> Issue-ID => voters[]
     */
    public array $asyncVotersByIssue = [];

    public function mount(string $inviteCode): RedirectResponse|LivewireRedirector|null
    {
        $this->inviteCode = $inviteCode;
        $this->session = Session::with(['issues', 'users', 'owner'])
            ->whereInviteCode($inviteCode)
            ->firstOrFail();

        if ($this->session->archived_at !== null) {
            return Redirect::route('session.archived', $inviteCode);
        }

        if (Auth::hasUser()) {
            $this->attachUserToSession();
        }

        if (!Gate::allows('vote_session', $this->session)) {
            abort(403);
        }

        $this->refreshAsyncProgress();

        return null;
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            // Keep owner progress in sync (does not leak vote values)
            "echo-presence:session.{$this->session->invite_code},.AsyncVoteUpdated" => 'refreshAsyncProgress',
            // Issues can change through imports/deletes
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'refreshAsyncProgress',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'refreshAsyncProgress',
            // Local refresh when voter saves/removes
            'refresh-async-lists' => 'refreshAsyncProgress',
        ];
    }

    public function refreshAsyncProgress(): void
    {
        // Always work with fresh relations
        $this->session->load(['issues', 'users']);

        $openIssueIds = $this->session->issues
            ->filter(fn($issue) => $issue->status !== IssueStatus::FINISHED && $issue->status !== IssueStatus::VOTING)
            ->pluck('id')
            ->all();

        if (empty($openIssueIds)) {
            $this->asyncVotersByIssue = [];
            return;
        }

        $votes = Vote::query()
            ->whereIn('issue_id', $openIssueIds)
            ->where('user_id', '!=', $this->session->owner_id)
            ->with('user:id,name')
            ->get(['issue_id', 'user_id']);

        $this->asyncVotersByIssue = $votes
            ->groupBy('issue_id')
            ->map(function ($votesForIssue) {
                return $votesForIssue
                    ->map(fn($vote) => [
                        'id' => $vote->user_id,
                        'name' => $vote->user?->name ?? 'Unknown',
                    ])
                    ->values()
                    ->all();
            })
            ->toArray();
    }

    /**
     * Revoke an async estimation (delete current user's vote) without opening the ticket.
     */
    public function revokeAsyncVote(int $issueId): void
    {
        if (Auth::id() === $this->session->owner_id || !Auth::check()) {
            return;
        }

        // Only allow for issues in this session and not in active live voting
        $issue = Issue::query()
            ->where('session_id', $this->session->id)
            ->where('id', $issueId)
            ->first();

        if (!$issue || $issue->status === IssueStatus::VOTING) {
            return;
        }

        Vote::query()
            ->where('user_id', Auth::id())
            ->where('issue_id', $issue->id)
            ->delete();

        broadcast(new AsyncVoteUpdated(
            $this->session->invite_code,
            $issue->id,
            Auth::id(),
            false,
        ))->toOthers();

        $this->dispatch('refresh-async-lists');
    }

    public function render(): View
    {
        $this->session->loadMissing(['issues', 'users', 'owner']);

        $isOwner = Auth::id() === $this->session->owner_id;

        /** @var Collection<int, \App\Models\Issue> $openIssues */
        $openIssues = $this->session->issues
            ->filter(fn($issue) => $issue->status !== IssueStatus::FINISHED && $issue->status !== IssueStatus::VOTING)
            ->sortBy('position')
            ->values();

        $eligibleVoterCount = $this->session->users
            ->filter(fn($u) => $u->id !== $this->session->owner_id)
            ->unique('id')
            ->count();

        $myVotesByIssue = [];
        $notVotedIssues = collect();
        $votedIssues = collect();

        if (!$isOwner && Auth::check() && $openIssues->isNotEmpty()) {
            $myVotesByIssue = Vote::query()
                ->where('user_id', Auth::id())
                ->whereIn('issue_id', $openIssues->pluck('id')->all())
                ->pluck('value', 'issue_id')
                ->all();

            $notVotedIssues = $openIssues->filter(fn($issue) => !array_key_exists($issue->id, $myVotesByIssue));
            $votedIssues = $openIssues->filter(fn($issue) => array_key_exists($issue->id, $myVotesByIssue));
        }

        return view('livewire.async-voting-page', [
            'isOwner' => $isOwner,
            'openIssues' => $openIssues,
            'eligibleVoterCount' => $eligibleVoterCount,
            'notVotedIssues' => $notVotedIssues,
            'votedIssues' => $votedIssues,
            'myVotesByIssue' => $myVotesByIssue,
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
