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

    /** @var array<string, string> */
    protected array $rules = [
        'issues.*.storypoints' => 'integer|in:0,1,2,3,5,8,13,20,40,100',
        'issueTitle' => 'required|max:255',
        'issueDescription' => '|max:255',
    ];

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => '$refresh',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => '$refresh',
        ];
    }

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

    public function formatJiraDescription(?string $description): string
    {
        if (empty($description)) {
            return '';
        }

        // Convert Confluence/Jira markup to HTML
        $html = (string) $description;

        // Convert headings h3. to h3
        $html = (string) preg_replace('/h3\.\s*(.+)/', '<h3 class="text-lg font-semibold mt-4 mb-2">$1</h3>', $html);

        // Convert bullet points * to <li>
        $html = (string) preg_replace('/^\*\s*(.+)$/m', '<li class="ml-4">$1</li>', $html);
        $html = (string) preg_replace('/(<li.*<\/li>)/s', '<ul class="list-disc ml-4 space-y-1">$1</ul>', $html);

        // Convert bold **text** to <strong>
        $html = (string) preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);

        // Convert italic _text_ to <em>
        $html = (string) preg_replace('/_(.+?)_/', '<em>$1</em>', $html);

        // Convert code `text` to <code>
        $html = (string) preg_replace('/`(.+?)`/', '<code class="bg-gray-100 px-1 rounded text-sm">$1</code>', $html);

        // Convert panels {panel} to divs
        $html = (string) preg_replace('/\{panel:bgColor=#deebff\}/', '<div class="bg-blue-50 border-l-4 border-blue-400 p-3 my-2">', $html);
        $html = (string) preg_replace('/\{panel\}/', '</div>', $html);

        // Convert images !image.png! to placeholder
        $html = (string) preg_replace('/!([^|]+\.png)\|width=(\d+),alt="([^"]+)"!/', '<div class="bg-gray-100 border rounded p-2 my-2 text-center text-sm text-gray-600">📷 Image: $3 ($1)</div>', $html);

        // Convert account references [~accountid:...] to @username
        $html = (string) preg_replace('/\[~accountid:[^\]]+\]/', '@user', $html);

        // Convert line breaks
        $html = nl2br($html);

        return $html;
    }
}
