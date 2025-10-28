<?php

namespace App\Livewire;

use App\Events\IssueAdded;
use App\Models\Issue;
use App\Models\Session;
use App\Services\JiraService;
use JiraRestApi\JiraException;
use Livewire\Component;

class JiraImport extends Component
{
    public Session $session;
    public string $projectKey = '';
    public string $status = '';
    public bool $isLoading = false;
    public string $message = '';
    public string $messageType = '';

    /** @var array<string, string> */
    protected array $rules = [
        'projectKey' => 'required|string|max:10',
        'status' => 'required|string|max:50',
    ];

    /** @var array<string, string> */
    protected array $messages = [
        'projectKey.required' => 'Project key is required.',
        'status.required' => 'Status is required.',
    ];

    public function mount(Session $session): void
    {
        $this->session = $session;
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.jira-import');
    }

    public function importTickets(): void
    {
        $this->validate();

        $this->isLoading = true;
        $this->message = '';
        $this->messageType = '';

        try {
            $jiraService = app(JiraService::class);
            $jiraIssues = $jiraService->searchIssuesByProjectAndStatus($this->projectKey, $this->status);

            if (empty($jiraIssues)) {
                $this->message = 'No tickets found with the specified criteria.';
                $this->messageType = 'warning';
                $this->isLoading = false;
                return;
            }

            $importedCount = 0;
            $skippedCount = 0;

            foreach ($jiraIssues as $jiraIssue) {
                $issueData = $jiraService->mapJiraIssueToArray($jiraIssue);
                $issueData['session_id'] = $this->session->id;
                $issueData['status'] = Issue::STATUS_NEW;

                // Check for duplicates by jira_key
                if (Issue::where('jira_key', $issueData['jira_key'])->exists()) {
                    $skippedCount++;
                    continue;
                }

                $issue = Issue::create($issueData);
                broadcast(new IssueAdded($issue))->toOthers();
                $importedCount++;
            }

            if ($importedCount > 0) {
                $this->message = "Successfully imported {$importedCount} tickets"
                    . ($skippedCount > 0 ? " ({$skippedCount} duplicates skipped)" : '') . '.';
                $this->messageType = 'success';
            } else {
                $this->message = 'All tickets were already imported (duplicates skipped).';
                $this->messageType = 'info';
            }

            // Reset form
            $this->projectKey = '';
            $this->status = '';

        } catch (JiraException $e) {
            $this->message = 'Failed to connect to Jira. Please check your credentials and try again.';
            $this->messageType = 'error';
        } catch (\Exception $e) {
            $this->message = 'An unexpected error occurred. Please try again.';
            $this->messageType = 'error';
        }

        $this->isLoading = false;
    }

    /** @return array<string, string> */
    public function getStatusOptionsProperty(): array
    {
        return [
            'In Estimation' => 'In Estimation',
        ];
    }
}
