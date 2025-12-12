<?php

declare(strict_types=1);

namespace App\Livewire\V2\Traits;

use App\Enums\IssueStatus;
use App\Events\IssueAdded;
use App\Models\Issue;
use App\Services\JiraService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Trait für Jira-Import-Funktionalität.
 *
 * Verwaltet das Laden und Importieren von Jira-Tickets.
 */
trait HandlesJiraImport
{
    /** @var array<int, array{id: string, name: string, jql: string}> */
    public array $jiraFilters = [];
    public bool $jiraFiltersLoaded = false;
    public bool $jiraLoading = false;
    public string $jiraInput = '';
    public string $jiraError = '';
    public string $jiraSuccess = '';

    /** @var array<int, array{key: string, title: string, description: ?string, url: string, estimate_unit: string, issue_type?: ?string, alreadyImported: bool}> */
    public array $jiraTickets = [];

    /** @var array<string> */
    public array $selectedJiraTickets = [];

    public bool $jiraRefreshing = false;

    /**
     * Initialisiert Jira-Filter aus der Session.
     */
    protected function initializeJiraFilters(): void
    {
        $sessionKey = 'jira_filters_' . Auth::id();
        $cached = session($sessionKey);

        if ($cached !== null) {
            $this->jiraFilters = $cached;
            $this->jiraFiltersLoaded = true;
        }
    }

    /**
     * Hook: Auto-Load Filter wenn Jira-Tab geöffnet wird.
     */
    protected function onJiraTabOpened(): void
    {
        if (!$this->jiraFiltersLoaded && $this->hasJiraCredentials()) {
            $this->loadJiraFilters();
        }
    }

    /**
     * Prüft ob der User Jira-Credentials hat.
     */
    public function hasJiraCredentials(): bool
    {
        $user = Auth::user();

        return $user && $user->jira_url && $user->jira_user && $user->jira_api_key;
    }

    /**
     * Lädt die Favoriten-Filter des Users aus Jira.
     */
    public function loadJiraFilters(bool $forceRefresh = false): void
    {
        if (!$this->hasJiraCredentials()) {
            return;
        }

        if ($this->jiraFiltersLoaded && !$forceRefresh) {
            return;
        }

        $this->jiraLoading = true;
        $this->jiraError = '';

        try {
            $jiraService = new JiraService(Auth::user());
            $this->jiraFilters = $jiraService->getFavoriteFilters();
            $this->jiraFiltersLoaded = true;

            $sessionKey = 'jira_filters_' . Auth::id();
            session([$sessionKey => $this->jiraFilters]);
        } catch (\Exception $e) {
            Log::error('Failed to load Jira filters: ' . $e->getMessage());
            $this->jiraError = 'Filter konnten nicht geladen werden.';
        }

        $this->jiraLoading = false;
    }

    /**
     * Erzwingt ein Neuladen der Jira-Filter.
     */
    public function refreshJiraFilters(): void
    {
        $this->jiraFiltersLoaded = false;
        $this->loadJiraFilters(true);
    }

    /**
     * Lädt Tickets aus einem Jira-Filter.
     */
    public function loadFromFilter(string $filterId): void
    {
        if (!$this->hasJiraCredentials()) {
            return;
        }

        $this->jiraLoading = true;
        $this->jiraError = '';
        $this->jiraTickets = [];
        $this->selectedJiraTickets = [];

        try {
            $jiraService = new JiraService(Auth::user());
            $jql = $jiraService->getFilterJql($filterId);

            if (!$jql) {
                $this->jiraError = 'Filter-JQL konnte nicht geladen werden.';
                $this->jiraLoading = false;

                return;
            }

            $this->loadTicketsFromJql($jql, $jiraService);
        } catch (\Exception $e) {
            Log::error('Failed to load from Jira filter: ' . $e->getMessage());
            $this->jiraError = 'Tickets konnten nicht geladen werden.';
        }

        $this->jiraLoading = false;
    }

    /**
     * Lädt Tickets basierend auf User-Input (URL, Keys, JQL).
     */
    public function loadFromInput(): void
    {
        if (!$this->hasJiraCredentials() || empty(trim($this->jiraInput))) {
            return;
        }

        $this->jiraLoading = true;
        $this->jiraError = '';
        $this->jiraTickets = [];
        $this->selectedJiraTickets = [];

        try {
            $jiraService = new JiraService(Auth::user());
            $parsed = $jiraService->parseJiraInput($this->jiraInput);

            switch ($parsed['type']) {
                case 'filter':
                    $jql = $jiraService->getFilterJql($parsed['value']);
                    if ($jql) {
                        $this->loadTicketsFromJql($jql, $jiraService);
                    } else {
                        $this->jiraError = 'Filter nicht gefunden.';
                    }
                    break;

                case 'jql':
                    $this->loadTicketsFromJql($parsed['value'], $jiraService);
                    break;

                case 'keys':
                    $issues = $jiraService->getIssuesByKeys($parsed['value']);
                    $this->mapIssuesToTickets($issues, $jiraService);
                    break;

                default:
                    $this->jiraError = 'Eingabe nicht erkannt. Bitte Jira-URL, Filter-URL oder Issue-Keys eingeben.';
            }
        } catch (\Exception $e) {
            Log::error('Failed to load from Jira input: ' . $e->getMessage());
            $this->jiraError = 'Tickets konnten nicht geladen werden: ' . $e->getMessage();
        }

        $this->jiraLoading = false;
    }

    /**
     * Lädt Tickets per JQL.
     */
    private function loadTicketsFromJql(string $jql, JiraService $jiraService): void
    {
        $issues = $jiraService->searchByJql($jql);
        $this->mapIssuesToTickets($issues, $jiraService);
    }

    /**
     * Mapped Jira-Issues zu Ticket-Array.
     *
     * @param array<object> $issues
     */
    private function mapIssuesToTickets(array $issues, JiraService $jiraService): void
    {
        $this->jiraTickets = [];

        foreach ($issues as $issue) {
            $mapped = $jiraService->mapJiraIssueToArray($issue);

            $alreadyImported = Issue::query()
                ->where('jira_key', $mapped['jira_key'])
                ->where('session_id', $this->session->id)
                ->exists();

            $this->jiraTickets[] = [
                'key' => $mapped['jira_key'],
                'title' => $mapped['title'],
                'description' => $mapped['description'],
                'url' => $mapped['jira_url'],
                'estimate_unit' => $mapped['estimate_unit'] ?? 'sp',
                'issue_type' => $mapped['issue_type'] ?? null,
                'alreadyImported' => $alreadyImported,
            ];
        }

        if (empty($this->jiraTickets)) {
            $this->jiraError = 'Keine Tickets gefunden.';
        }
    }

    /**
     * Alle nicht-importierten Tickets auswählen.
     */
    public function selectAllJiraTickets(): void
    {
        $this->selectedJiraTickets = collect($this->jiraTickets)
            ->filter(fn($t) => !$t['alreadyImported'])
            ->pluck('key')
            ->all();
    }

    /**
     * Auswahl aufheben.
     */
    public function deselectAllJiraTickets(): void
    {
        $this->selectedJiraTickets = [];
    }

    /**
     * Importiert die ausgewählten Jira-Tickets.
     */
    public function importSelectedJiraTickets(): void
    {
        if (Auth::id() !== $this->session->owner_id || empty($this->selectedJiraTickets)) {
            return;
        }

        $this->jiraLoading = true;
        $this->jiraError = '';
        $this->jiraSuccess = '';

        $importedCount = 0;
        $maxPosition = Issue::query()
            ->where('session_id', $this->session->id)
            ->max('position') ?? -1;

        foreach ($this->jiraTickets as &$ticket) {
            if (!in_array($ticket['key'], $this->selectedJiraTickets)) {
                continue;
            }

            if ($ticket['alreadyImported']) {
                continue;
            }

            $maxPosition++;

            Issue::create([
                'title' => $ticket['title'],
                'description' => $ticket['description'],
                'session_id' => $this->session->id,
                'status' => IssueStatus::NEW,
                'position' => $maxPosition,
                'jira_key' => $ticket['key'],
                'jira_url' => $ticket['url'],
                'estimate_unit' => $ticket['estimate_unit'] ?? 'sp',
                'issue_type' => $ticket['issue_type'] ?? null,
            ]);

            $ticket['alreadyImported'] = true;
            $importedCount++;
        }

        $this->selectedJiraTickets = [];
        $this->jiraSuccess = "{$importedCount} Ticket(s) importiert.";
        $this->jiraLoading = false;

        broadcast(new IssueAdded($this->session->invite_code))->toOthers();
    }

    /**
     * Setzt den Jira-Import-State zurück.
     */
    public function resetJiraImport(): void
    {
        $this->jiraTickets = [];
        $this->selectedJiraTickets = [];
        $this->jiraError = '';
        $this->jiraSuccess = '';
        $this->jiraInput = '';
    }

    /**
     * Refresh a single imported issue from Jira (title/description/url/type).
     * Best-effort: failures are shown in $jiraError and do not break the session.
     */
    public function refreshIssueFromJira(int $issueId): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->hasJiraCredentials()) {
            return;
        }

        $issue = Issue::query()
            ->where('session_id', $this->session->id)
            ->whereKey($issueId)
            ->first();

        if (!$issue || empty($issue->jira_key)) {
            return;
        }

        $this->jiraRefreshing = true;
        $this->jiraError = '';
        $this->jiraSuccess = '';

        try {
            $jiraService = new JiraService(Auth::user());
            $jiraIssue = $jiraService->getIssueByKey($issue->jira_key);

            if (!$jiraIssue) {
                $this->jiraError = 'Ticket konnte nicht aus Jira geladen werden.';
                $this->jiraRefreshing = false;
                return;
            }

            $mapped = $jiraService->mapJiraIssueToArray($jiraIssue);

            $issue->title = $mapped['title'] ?? $issue->title;
            $issue->description = $mapped['description'] ?? $issue->description;
            $issue->jira_url = $mapped['jira_url'] ?? $issue->jira_url;
            $issue->estimate_unit = $mapped['estimate_unit'] ?? ($issue->estimate_unit ?? 'sp');
            $issue->issue_type = $mapped['issue_type'] ?? $issue->issue_type;
            $issue->save();

            // Keep state fresh
            $this->session->load('issues');

            $this->jiraSuccess = "Ticket {$issue->jira_key} aktualisiert.";
        } catch (\Throwable $e) {
            Log::error('Failed to refresh Jira issue: ' . $e->getMessage());
            $this->jiraError = 'Ticket konnte nicht aktualisiert werden.';
        }

        $this->jiraRefreshing = false;
    }

    /**
     * Refresh all imported issues in this session that have a jira_key.
     */
    public function refreshAllJiraIssues(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->hasJiraCredentials()) {
            return;
        }

        $keysById = Issue::query()
            ->where('session_id', $this->session->id)
            ->whereNotNull('jira_key')
            ->pluck('jira_key', 'id')
            ->all();

        if (empty($keysById)) {
            return;
        }

        $this->jiraRefreshing = true;
        $this->jiraError = '';
        $this->jiraSuccess = '';

        $updated = 0;

        try {
            $jiraService = new JiraService(Auth::user());
            $keys = array_values(array_map('strval', $keysById));
            $jiraIssues = $jiraService->getIssuesByKeys($keys);

            foreach ($jiraIssues as $jiraIssue) {
                $mapped = $jiraService->mapJiraIssueToArray($jiraIssue);
                $key = (string) ($mapped['jira_key'] ?? '');
                if ($key === '') {
                    continue;
                }

                $issueId = array_search($key, $keysById, true);
                if ($issueId === false) {
                    continue;
                }

                $issue = Issue::query()
                    ->where('session_id', $this->session->id)
                    ->whereKey((int) $issueId)
                    ->first();

                if (!$issue) {
                    continue;
                }

                $issue->title = $mapped['title'] ?? $issue->title;
                $issue->description = $mapped['description'] ?? $issue->description;
                $issue->jira_url = $mapped['jira_url'] ?? $issue->jira_url;
                $issue->estimate_unit = $mapped['estimate_unit'] ?? ($issue->estimate_unit ?? 'sp');
                $issue->issue_type = $mapped['issue_type'] ?? $issue->issue_type;
                $issue->save();

                $updated++;
            }

            $this->session->load('issues');
            $this->jiraSuccess = "{$updated} Ticket(s) aktualisiert.";
        } catch (\Throwable $e) {
            Log::error('Failed to refresh all Jira issues: ' . $e->getMessage());
            $this->jiraError = 'Tickets konnten nicht aktualisiert werden.';
        }

        $this->jiraRefreshing = false;
    }
}
