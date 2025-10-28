<?php

namespace App\Livewire;

use App\Events\IssueAdded;
use App\Events\IssueDeleted;
use App\Models\Issue;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class IssueManagement extends Component
{
    public Collection $issues;

    public string $issueTitle;

    public string $issueDescription = '';

    public Session $session;

    /** @var array */
    protected $listeners = ['refreshIssues' => '$refresh'];

    public function mount(string $inviteCode): void
    {
        $this->session = Session::where('invite_code', $inviteCode)->firstOrFail();
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
    }

    public function render(): View
    {
        return view('livewire.issue-management');
    }

    public function addIssue(): void
    {
        $this->validate([
            'issueTitle' => 'required|max:255',
        ]);

        $issue = Issue::create([
            'title' => $this->issueTitle,
            'description' => $this->issueDescription,
            'session_id' => $this->session->id, // Replace with the actual session_id
        ]);

        $this->issueTitle = '';
        $this->issueDescription = '';

        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
        $this->dispatch('refreshIssues');
        broadcast(new IssueAdded($issue))->toOthers();
    }

    public function editIssue(): void
    {
        $this->dispatch('refreshIssues');
    }

    public function deleteIssue(int $issueId): void
    {
        $issue = Issue::findOrFail($issueId);
        $issue->delete();

        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
        $this->dispatch('refreshIssues');
        event(new IssueDeleted($this->session->invite_code));
    }
}
