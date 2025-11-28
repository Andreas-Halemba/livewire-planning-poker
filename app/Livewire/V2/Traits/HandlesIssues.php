<?php

declare(strict_types=1);

namespace App\Livewire\V2\Traits;

use App\Enums\IssueStatus;
use App\Events\IssueAdded;
use App\Events\IssueDeleted;
use App\Events\IssueOrderChanged;
use App\Models\Issue;
use Illuminate\Support\Facades\Auth;

/**
 * Trait für Issue-Management.
 *
 * Verwaltet CRUD-Operationen für Issues und Drawer-State.
 */
trait HandlesIssues
{
    // ===== Drawer State =====
    public bool $drawerOpen = false;
    public string $drawerTab = 'manual';
    public string $newIssueTitle = '';
    public string $newIssueDescription = '';
    public string $newIssueJiraKey = '';
    public string $newIssueJiraUrl = '';

    /**
     * Gibt die Issue Event Listeners zurück.
     *
     * @return array<string, string>
     */
    protected function getIssueListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueOrderChanged" => 'handleIssueOrderChanged',
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueAdded',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueDeleted',
        ];
    }

    // ===== Event Handlers =====

    public function handleIssueOrderChanged(): void
    {
        $this->session->load('issues');
    }

    public function handleIssueAdded(): void
    {
        $this->session->load('issues');
    }

    public function handleIssueDeleted(): void
    {
        $this->session->load('issues');
    }

    // ===== Actions =====

    /**
     * Fügt ein neues Issue hinzu (nur Owner).
     */
    public function addIssue(): void
    {
        if (Auth::id() !== $this->session->owner_id) {
            return;
        }

        $this->validate([
            'newIssueTitle' => 'required|string|max:255',
            'newIssueDescription' => 'nullable|string|max:2000',
            'newIssueJiraKey' => 'nullable|string|max:50',
            'newIssueJiraUrl' => 'nullable|url|max:500',
        ]);

        $maxPosition = Issue::query()
            ->where('session_id', $this->session->id)
            ->max('position') ?? -1;

        Issue::create([
            'title' => $this->newIssueTitle,
            'description' => $this->newIssueDescription ?: null,
            'session_id' => $this->session->id,
            'status' => IssueStatus::NEW,
            'position' => $maxPosition + 1,
            'jira_key' => $this->newIssueJiraKey ?: null,
            'jira_url' => $this->newIssueJiraUrl ?: null,
        ]);

        $this->resetIssueForm();
        $this->drawerOpen = false;

        broadcast(new IssueAdded($this->session->invite_code))->toOthers();
    }

    /**
     * Löscht ein Issue aus der Session (nur Owner).
     */
    public function deleteIssue(int $issueId): void
    {
        if (Auth::id() !== $this->session->owner_id) {
            return;
        }

        $issue = Issue::find($issueId);
        if (!$issue || $issue->session_id !== $this->session->id) {
            return;
        }

        // Nicht löschen wenn gerade im Voting
        if ($issue->status === IssueStatus::VOTING) {
            return;
        }

        $issue->delete();

        broadcast(new IssueDeleted($this->session->invite_code))->toOthers();
    }

    /**
     * Aktualisiert die Reihenfolge der Issues (nur Owner).
     *
     * @param array<int> $orderedIds Array von Issue-IDs in neuer Reihenfolge
     */
    public function updateIssueOrder(array $orderedIds): void
    {
        if (Auth::id() !== $this->session->owner_id) {
            return;
        }

        foreach ($orderedIds as $position => $issueId) {
            Issue::query()
                ->where('id', $issueId)
                ->where('session_id', $this->session->id)
                ->update(['position' => $position]);
        }

        broadcast(new IssueOrderChanged($this->session->invite_code))->toOthers();
    }

    /**
     * Setzt das Issue-Formular zurück.
     */
    protected function resetIssueForm(): void
    {
        $this->newIssueTitle = '';
        $this->newIssueDescription = '';
        $this->newIssueJiraKey = '';
        $this->newIssueJiraUrl = '';
    }
}
