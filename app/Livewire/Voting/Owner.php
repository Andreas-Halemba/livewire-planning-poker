<?php

namespace App\Livewire\Voting;

use App\Enums\IssueStatus;
use App\Events\HideVotes;
use App\Events\IssueAdded;
use App\Events\IssueCanceled;
use App\Events\IssueDeleted;
use App\Events\IssueSelected;
use App\Events\RevealVotes;
use App\Models\Issue;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Owner extends Component
{
    use InspectorLivewire;

    public Session $session;

    public string $issueTitle = '';

    public string $issueDescription = '';

    public string $activeTab = 'open';

    public ?string $selectedEstimate = null;

    public ?int $customEstimate = null;

    public bool $votesRevealed = false;

    public int $activeParticipantsCount = 0;

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
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleAddVote',
            'refresh-voter-lists' => 'handleIssueAdded',
            'participants-count-updated' => 'updateActiveParticipantsCount',
        ];
    }

    #[Computed]
    /**
     * @return Collection<int, \App\Models\Issue>
     */
    public function issues(): Collection
    {
        return $this->session->issues()->get();
    }

    public function handleIssueAdded(): void
    {
        $this->refreshIssues();
    }

    private function refreshIssues(): void
    {
        $this->session->refresh();
        unset($this->issues);
    }

    public function handleIssueDeleted(): void
    {
        $this->refreshIssues();
    }

    public function handleRevealVotes(): void
    {
        $this->votesRevealed = true;
    }

    public function handleHideVotes(): void
    {
        $this->reset(['votesRevealed', 'selectedEstimate', 'customEstimate']);
    }

    public function handleIssueSelected(): void
    {
        $this->reset(['votesRevealed', 'selectedEstimate', 'customEstimate']);
    }

    public function handleIssueCanceled(): void
    {
        $this->reset(['votesRevealed', 'selectedEstimate', 'customEstimate']);
    }

    public function handleAddVote(): void
    {
        // Check if all participants have voted and auto-reveal if so
        // $this->checkAndAutoRevealVotes();
    }

    public function updateActiveParticipantsCount(int $count): void
    {
        $this->activeParticipantsCount = $count;
        // Skip render as this is just updating a counter value
        $this->skipRender();
    }

    public function render(): View
    {
        // Check if all participants have voted and auto-reveal if so
        $this->checkAndAutoRevealVotes();

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

        $issue->status = IssueStatus::FINISHED;
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

        broadcast(new IssueCanceled($this->session->invite_code))->toOthers();
        // Update the issues collection to sync with database changes
        $this->issues = $this->session->issues()->get();
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
        $issue->status = IssueStatus::NEW;
        $issue->save();
        broadcast(new IssueCanceled($this->session->invite_code))->toOthers();
        // Update the issues collection to sync with database changes
        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
    }

    public function addIssue(): void
    {
        $issue = Issue::query()->create([
            'title' => $this->issueTitle,
            'description' => $this->issueDescription,
            'session_id' => $this->session->id,
            'status' => IssueStatus::NEW,
        ]);

        $this->issueTitle = '';
        $this->issueDescription = '';

        $this->issues = Issue::query()->whereBelongsTo($this->session)->get();
        broadcast(new IssueAdded($this->session->invite_code))->toOthers();
    }

    public function deleteIssue(Issue $issue): void
    {
        // Prevent deletion if issue is currently being voted on
        if ($issue->status === IssueStatus::VOTING) {
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
        Issue::whereStatus(IssueStatus::VOTING)
            ->whereSessionId($this->session->id)
            ->update(['status' => IssueStatus::NEW]);
    }

    private function setIssueStatusToVoting(int $id): void
    {
        $issue = Issue::query()->whereId($id)->firstOrFail();
        $issue->status = IssueStatus::VOTING;
        $issue->save();
        $this->votesRevealed = false; // Reset votes revealed when starting new voting
        broadcast(new IssueSelected($this->session->invite_code))->toOthers();
    }

    public function revealVotes(): void
    {
        $currentIssue = $this->session->currentIssue();
        if ($currentIssue) {
            $this->votesRevealed = true;
            broadcast(new RevealVotes($this->session->invite_code))->toOthers();
            $this->dispatch('votes-revealed');
        }
    }

    public function restartVoting(): void
    {
        $currentIssue = $this->session->currentIssue();
        if ($currentIssue) {
            // Delete all votes for the current issue
            $currentIssue->votes()->delete();

            // Reset votes revealed state
            $this->votesRevealed = false;
            $this->selectedEstimate = null;
            $this->customEstimate = null;

            // Broadcast HideVotes event to all participants to reset their UI
            broadcast(new HideVotes($this->session->invite_code))->toOthers();
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

    /**
     * @todo check which users are in the session and have voted. Add the async votes after all present users have voted.
     *
     * @return void
     */
    private function checkAndAutoRevealVotes(): void
    {
        // Only auto-reveal if not already revealed
        if ($this->votesRevealed) {
            return;
        }

        $currentIssue = $this->session->currentIssue();
        if (!$currentIssue) {
            return;
        }

        // Use active participants count from SessionParticipants component
        // This tracks who is actually online/present in the session (excluding owner)
        $activeVotersCount = $this->activeParticipantsCount > 0 ? $this->activeParticipantsCount - 1 : 0; // Subtract 1 for owner



        // If no active voters, don't auto-reveal
        if ($activeVotersCount === 0) {
            return;
        }

        // Count votes for current issue from non-owner participants only
        $voteCount = $currentIssue->votes()
            ->where('user_id', '!=', $this->session->owner_id)
            ->count();

        // If all active voter participants have voted, auto-reveal
        if ($voteCount >= $activeVotersCount) {
            $this->revealVotes();
        }
    }
}
