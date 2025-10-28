<?php

namespace App\Livewire\Voting;

use App\Events\IssueAdded;
use App\Events\IssueCanceled;
use App\Events\IssueDeleted;
use App\Events\IssueSelected;
use App\Models\Issue;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class Owner extends Component
{
    public Session $session;

    /** @var Collection<int,Issue> */
    public Collection $issues;

    public string $issueTitle = '';

    public string $issueDescription = '';

    protected array $rules = [
        'issues.*.storypoints' => 'integer|in:0,1,2,3,5,8,13,20,40,100',
        'issueTitle' => 'required|max:255',
        'issueDescription' => '|max:255',
    ];

    public function render(): View
    {
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
        return view('livewire.voting.owner');
    }

    public function addPointsToIssue(int $id): void
    {
        $issue = Issue::query()->whereId($id)->firstOrFail();
        $issue->storypoints = $this->issues->firstOrFail('id', $id)->storypoints;
        $issue->status = Issue::STATUS_FINISHED;
        $issue->save();
        broadcast(new IssueCanceled($issue));
    }

    public function voteIssue(int $id): void
    {
        $this->resetIssuesStatus();
        $this->setIssueStatusToVoting($id);
    }

    public function cancelIssue(int $id): void
    {
        $issue = Issue::query()->whereId($id)->firstOrFail();
        $issue->status = Issue::STATUS_NEW;
        $issue->save();
        broadcast(new IssueCanceled($issue));
    }

    public function addIssue(): void
    {
        $issue = Issue::query()->create([
            'title' => $this->issueTitle,
            'description' => $this->issueDescription,
            'session_id' => $this->session->id,
            'status' => Issue::STATUS_NEW,
        ]);

        $this->issueTitle = '';
        $this->issueDescription = '';

        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
        $this->dispatch('refreshIssues');
        broadcast(new IssueAdded($issue))->toOthers();
    }

    public function deleteIssue(Issue $issue): void
    {
        $issue->forceDelete();
        broadcast(new IssueDeleted($this->session->invite_code));
    }

    private function resetIssuesStatus(): void
    {
        Issue::whereStatus(Issue::STATUS_VOTING)
            ->whereSessionId($this->session->id)
            ->update(['status' => Issue::STATUS_NEW]);
    }

    private function setIssueStatusToVoting(int $id): void
    {
        $issue = Issue::query()->whereId($id)->firstOrFail();
        $issue->status = Issue::STATUS_VOTING;
        $issue->save();
        broadcast(new IssueSelected($issue));
    }
}
