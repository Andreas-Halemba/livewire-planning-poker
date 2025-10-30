<?php

namespace App\Livewire\Voting;

use App\Events\IssueAdded;
use App\Events\IssueCanceled;
use App\Events\IssueDeleted;
use App\Events\IssueSelected;
use App\Events\RevealVotes;
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

    public string $activeTab = 'open';

    public ?string $selectedEstimate = null;

    public ?int $customEstimate = null;

    public bool $votesRevealed = false;

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
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueAdded',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueDeleted',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'handleRevealVotes',
            "echo-presence:session.{$this->session->invite_code},.HideVotes" => 'handleHideVotes',
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
        ];
    }

    public function handleIssueAdded(): void
    {
        // Just reload the issues collection without full refresh
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
    }

    public function handleIssueDeleted(): void
    {
        // Just reload the issues collection without full refresh
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
    }

    public function handleRevealVotes(): void
    {
        $this->votesRevealed = true;
    }

    public function handleHideVotes(): void
    {
        $this->votesRevealed = false;
        $this->selectedEstimate = null;
        $this->customEstimate = null;
    }

    public function handleIssueSelected(): void
    {
        $this->votesRevealed = false;
        $this->selectedEstimate = null;
        $this->customEstimate = null;
    }

    public function handleIssueCanceled(): void
    {
        $this->votesRevealed = false;
        $this->selectedEstimate = null;
        $this->customEstimate = null;
    }

    public function render(): View
    {
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();

        // Get grouped votes for current voting issue (only if revealed)
        $groupedVotes = [];
        $currentIssue = $this->session->currentIssue();
        if ($currentIssue && $this->votesRevealed) {
            $votes = $currentIssue->votes()->with('user')->get();
            foreach ($votes as $vote) {
                if ($vote->value !== null) {
                    $value = (string) $vote->value;
                    if (!isset($groupedVotes[$value])) {
                        $groupedVotes[$value] = [
                            'count' => 0,
                            'participants' => [],
                        ];
                    }
                    $groupedVotes[$value]['count']++;
                    $groupedVotes[$value]['participants'][] = $vote->user->name;
                }
            }
            // Sort by value
            ksort($groupedVotes, SORT_NUMERIC);
        }

        return view('livewire.voting.owner', [
            'groupedVotes' => $groupedVotes,
            'currentIssue' => $currentIssue,
        ]);
    }

    public function addPointsToIssue(int $id, ?int $customPoints = null): void
    {
        $issue = Issue::query()->whereId($id)->firstOrFail();

        // Use custom points if provided, otherwise use the value from the issues collection
        if ($customPoints !== null) {
            $issue->storypoints = $customPoints;
        } else {
            $issue->storypoints = $this->issues->firstOrFail('id', $id)->storypoints;
        }

        $issue->status = Issue::STATUS_FINISHED;
        $issue->save();

        // Update story points in Jira if issue has Jira key and owner has Jira credentials
        if ($issue->jira_key && $issue->storypoints !== null) {
            $owner = $this->session->owner()->first();
            if ($owner && $owner->jira_url && $owner->jira_user && $owner->jira_api_key) {
                try {
                    $jiraService = new \App\Services\JiraService($owner);
                    $success = $jiraService->updateStoryPoints($issue->jira_key, $issue->storypoints);
                    if (!$success) {
                        \Illuminate\Support\Facades\Log::warning("Jira story points update returned false for {$issue->jira_key}");
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to update Jira story points for {$issue->jira_key}: " . $e->getMessage());
                    // Don't fail the entire operation if Jira update fails
                }
            }
        }

        broadcast(new IssueCanceled($issue))->toOthers();
        // Update the issues collection to sync with database changes
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
    }

    public function voteIssue(int $id): void
    {
        $this->resetIssuesStatus();
        $this->setIssueStatusToVoting($id);
        // Update the issues collection to sync with database changes
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
    }

    public function cancelIssue(int $id): void
    {
        $issue = Issue::query()->whereId($id)->firstOrFail();
        $issue->status = Issue::STATUS_NEW;
        $issue->save();
        broadcast(new IssueCanceled($issue))->toOthers();
        // Update the issues collection to sync with database changes
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
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
        broadcast(new IssueAdded($issue))->toOthers();
    }

    public function deleteIssue(Issue $issue): void
    {
        // Prevent deletion if issue is currently being voted on
        if ($issue->status === Issue::STATUS_VOTING) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Ein Issue kann nicht gelöscht werden, während eine Schätzung läuft.',
            ]);
            return;
        }

        $issue->forceDelete();
        // Update the issues collection to sync with database changes
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
        broadcast(new IssueDeleted($this->session->invite_code))->toOthers();
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
        $this->votesRevealed = false; // Reset votes revealed when starting new voting
        broadcast(new IssueSelected($issue))->toOthers();
    }

    public function revealVotes(): void
    {
        $currentIssue = $this->session->currentIssue();
        if ($currentIssue) {
            $this->votesRevealed = true;
            broadcast(new RevealVotes($this->session))->toOthers();
            $this->dispatch('votes-revealed');
        }
    }

    public function selectEstimate(string $value): void
    {
        $this->selectedEstimate = $value;
        $this->customEstimate = null;
    }

    public function confirmEstimate(int $issueId): void
    {
        $finalEstimate = $this->customEstimate ?? (int) $this->selectedEstimate;

        if (!$finalEstimate || $finalEstimate <= 0) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Bitte wähle eine Schätzung aus oder gib einen Wert ein.',
            ]);
            return;
        }

        $this->addPointsToIssue($issueId, $finalEstimate);

        // Reset
        $this->selectedEstimate = null;
        $this->customEstimate = null;
        $this->votesRevealed = false;
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
