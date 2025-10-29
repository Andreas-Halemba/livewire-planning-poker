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
    public bool $showModal = false;

    /** @var array<int, array<string, mixed>> */
    public array $availableTickets = [];

    /** @var array<string> */
    public array $selectedTickets = [];

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

    public function loadTickets(): void
    {
        $this->validate();

        $this->isLoading = true;
        $this->message = '';
        $this->messageType = '';
        $this->availableTickets = [];
        $this->selectedTickets = [];

        try {
            $jiraService = app(JiraService::class);
            $jiraIssues = $jiraService->searchIssuesByProjectAndStatus($this->projectKey, $this->status);

            if (empty($jiraIssues)) {
                $this->message = 'No tickets found with the specified criteria.';
                $this->messageType = 'warning';
                $this->isLoading = false;
                return;
            }

            // Prepare tickets for selection
            foreach ($jiraIssues as $jiraIssue) {
                $issueData = $jiraService->mapJiraIssueToArray($jiraIssue);

                // Check if already imported in THIS session
                $alreadyImported = Issue::where('jira_key', $issueData['jira_key'])
                    ->where('session_id', $this->session->id)
                    ->exists();

                $this->availableTickets[] = [
                    'key' => $issueData['jira_key'],
                    'title' => $issueData['title'],
                    'description' => $issueData['description'],
                    'url' => $issueData['jira_url'],
                    'already_imported' => $alreadyImported,
                ];
            }

            $this->showModal = true;
            $this->message = 'Tickets loaded. Please select the ones you want to import.';
            $this->messageType = 'success';

        } catch (JiraException $e) {
            $this->message = 'Failed to connect to Jira. Please check your credentials and try again.';
            $this->messageType = 'error';
        } catch (\Exception $e) {
            $this->message = 'An unexpected error occurred. Please try again.';
            $this->messageType = 'error';
        }

        $this->isLoading = false;
    }

    public function importSelectedTickets(): void
    {
        if (empty($this->selectedTickets)) {
            $this->message = 'Please select at least one ticket to import.';
            $this->messageType = 'warning';
            return;
        }

        $this->isLoading = true;
        $this->message = '';
        $this->messageType = '';

        try {
            $jiraService = app(JiraService::class);
            $importedCount = 0;
            $skippedCount = 0;

            foreach ($this->availableTickets as $ticket) {
                if (!in_array($ticket['key'], $this->selectedTickets)) {
                    continue;
                }

                // Skip if already imported
                if ($ticket['already_imported']) {
                    $skippedCount++;
                    continue;
                }

                $issueData = [
                    'title' => $ticket['title'],
                    'description' => $ticket['description'],
                    'jira_key' => $ticket['key'],
                    'jira_url' => $ticket['url'] ?? '',
                    'session_id' => $this->session->id,
                    'status' => Issue::STATUS_NEW,
                ];

                $issue = Issue::create($issueData);

                // Try to broadcast, but don't fail if it doesn't work
                try {
                    broadcast(new IssueAdded($issue));
                } catch (\Exception $broadcastException) {
                    \Log::warning('Failed to broadcast issue addition', [
                        'issueId' => $issue->id,
                        'error' => $broadcastException->getMessage(),
                    ]);
                }

                $importedCount++;

                // Update the ticket status in availableTickets
                foreach ($this->availableTickets as &$availableTicket) {
                    if ($availableTicket['key'] === $ticket['key']) {
                        $availableTicket['already_imported'] = true;
                        break;
                    }
                }
            }

            if ($importedCount > 0) {
                $this->message = "Successfully imported {$importedCount} tickets"
                    . ($skippedCount > 0 ? " ({$skippedCount} duplicates skipped)" : '') . '.';
                $this->messageType = 'success';

                // Clear selection but keep modal open to show updated status
                $this->selectedTickets = [];
            } else {
                $this->message = 'All selected tickets were already imported (duplicates skipped).';
                $this->messageType = 'warning';
                // Keep modal open so user can see the message
            }

        } catch (\Exception $e) {
            $this->message = 'An unexpected error occurred during import. Please try again.';
            $this->messageType = 'error';
            \Log::error('Jira import error: ' . $e->getMessage());
        }

        $this->isLoading = false;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->availableTickets = [];
        $this->selectedTickets = [];
    }

    public function toggleSelectAll(): void
    {
        if (count($this->selectedTickets) === count($this->availableTickets)) {
            $this->selectedTickets = [];
        } else {
            $this->selectedTickets = array_column($this->availableTickets, 'key');
        }
    }

    /** @return array<string, string> */
    public function getStatusOptionsProperty(): array
    {
        return [
            'In Estimation' => 'In Estimation',
        ];
    }
}
