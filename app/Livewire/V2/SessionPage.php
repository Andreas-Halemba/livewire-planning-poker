<?php

declare(strict_types=1);

namespace App\Livewire\V2;

use App\Enums\IssueStatus;
use App\Events\IssueSelected;
use App\Models\Issue;
use App\Models\Session;
use App\Models\Vote;
use App\Services\JiraService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

/**
 * V2 SessionPage - Schritt 2c: Mit Voting-Status.
 *
 * Zeigt alle Session-Mitglieder mit:
 * - Präsenz-Status (online/offline via Presence Channel)
 * - Voting-Status (wartet/abgestimmt/übersprungen)
 */
class SessionPage extends Component
{
    public Session $session;

    /** @var array<int> IDs der online User */
    public array $onlineUserIds = [];

    /** @var Issue|null Aktuell zu schätzendes Issue */
    public ?Issue $currentIssue = null;

    /** @var bool Wurden die Votes aufgedeckt? */
    public bool $votesRevealed = false;

    /** @var array<int> User-IDs die abgestimmt haben */
    public array $votedUserIds = [];

    /** @var array<int, string> Vote-Werte pro User-ID */
    public array $votesByUser = [];

    /** @var array<int> Verfügbare Voting-Karten */
    public array $cards = [1, 2, 3, 5, 8, 13, 21, 100];

    /** @var int|null Eigene Auswahl des aktuellen Users */
    public ?int $myVote = null;

    // ===== Drawer State =====
    public bool $drawerOpen = false;
    public string $drawerTab = 'manual';
    public string $newIssueTitle = '';
    public string $newIssueDescription = '';
    public string $newIssueJiraKey = '';
    public string $newIssueJiraUrl = '';

    // ===== Jira Import State =====
    /** @var array<int, array{id: string, name: string, jql: string}> */
    public array $jiraFilters = [];
    public bool $jiraFiltersLoaded = false;
    public bool $jiraLoading = false;
    public string $jiraInput = '';
    public string $jiraError = '';
    public string $jiraSuccess = '';
    /** @var array<int, array{key: string, title: string, description: ?string, url: string, alreadyImported: bool}> */
    public array $jiraTickets = [];
    /** @var array<string> */
    public array $selectedJiraTickets = [];

    public function mount(string $inviteCode): void
    {
        $this->session = Session::with(['issues', 'users', 'owner'])
            ->where('invite_code', $inviteCode)
            ->firstOrFail();

        // Aktueller User ist immer online
        if (Auth::check()) {
            $this->onlineUserIds = [Auth::id()];
        }

        // Initiales Laden des aktuellen Issues
        $this->loadCurrentIssue();

        // Jira-Filter aus Session laden (falls vorhanden)
        $this->loadJiraFiltersFromSession();
    }

    /**
     * Hook: Wird aufgerufen wenn drawerTab geändert wird.
     */
    public function updatedDrawerTab(string $value): void
    {
        // Auto-Load Jira-Filter wenn Jira-Tab geöffnet wird
        if ($value === 'jira' && !$this->jiraFiltersLoaded && $this->hasJiraCredentials()) {
            $this->loadJiraFilters();
        }
    }

    /**
     * Lädt Jira-Filter aus der Laravel Session (Cache).
     */
    private function loadJiraFiltersFromSession(): void
    {
        $sessionKey = 'jira_filters_' . Auth::id();
        $cached = session($sessionKey);

        if ($cached !== null) {
            $this->jiraFilters = $cached;
            $this->jiraFiltersLoaded = true;
        }
    }

    /**
     * Event Listeners für Presence + Voting Events.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            // Presence Events
            "echo-presence:session.{$this->session->invite_code},here" => 'handleUsersHere',
            "echo-presence:session.{$this->session->invite_code},joining" => 'handleUserJoining',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'handleUserLeaving',
            // Voting Events
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueSelected',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueCanceled',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleAddVote',
            "echo-presence:session.{$this->session->invite_code},.RevealVotes" => 'handleRevealVotes',
            "echo-presence:session.{$this->session->invite_code},.HideVotes" => 'handleHideVotes',
            // Issue Events
            "echo-presence:session.{$this->session->invite_code},.IssueOrderChanged" => 'handleIssueOrderChanged',
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueAdded',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueDeleted',
        ];
    }

    // ===== Presence Handlers =====

    /**
     * @param array<int, array{id: int}> $users
     */
    public function handleUsersHere(array $users): void
    {
        $this->onlineUserIds = collect($users)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param array{id: int} $user
     */
    public function handleUserJoining(array $user): void
    {
        if (isset($user['id']) && !in_array($user['id'], $this->onlineUserIds)) {
            $this->onlineUserIds[] = $user['id'];
        }
    }

    /**
     * @param array{id: int} $user
     */
    public function handleUserLeaving(array $user): void
    {
        if (isset($user['id'])) {
            $this->onlineUserIds = array_values(
                array_filter($this->onlineUserIds, fn($id) => $id !== $user['id']),
            );
        }
    }

    // ===== Voting Handlers =====

    public function handleIssueSelected(): void
    {
        $this->votesRevealed = false;
        $this->loadCurrentIssue();
    }

    public function handleIssueCanceled(): void
    {
        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
    }

    public function handleAddVote(): void
    {
        $this->loadVotedUsers();
    }

    public function handleRevealVotes(): void
    {
        $this->votesRevealed = true;
        $this->loadVotedUsers();
    }

    public function handleHideVotes(): void
    {
        $this->votesRevealed = false;
        $this->loadVotedUsers();
    }

    public function handleIssueOrderChanged(): void
    {
        // Session Issues neu laden, um neue Reihenfolge zu erhalten
        $this->session->load('issues');
    }

    public function handleIssueAdded(): void
    {
        // Session Issues neu laden, um neues Issue zu sehen
        $this->session->load('issues');
    }

    public function handleIssueDeleted(): void
    {
        // Session Issues neu laden
        $this->session->load('issues');
    }

    // ===== Owner Actions =====

    /**
     * Deckt die Votes auf (nur Owner).
     */
    public function revealVotes(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        $this->votesRevealed = true;
        $this->loadVotedUsers();

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\RevealVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Verdeckt die Votes wieder (nur Owner).
     */
    public function hideVotes(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        $this->votesRevealed = false;

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\HideVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Bricht das aktuelle Voting ab (nur Owner).
     */
    public function cancelVoting(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        // Issue zurück auf "new" setzen
        $this->currentIssue->status = IssueStatus::NEW;
        $this->currentIssue->save();

        // State zurücksetzen
        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\IssueCanceled($this->session->invite_code))->toOthers();
    }

    /**
     * Startet das Voting neu - löscht alle Votes (nur Owner).
     */
    public function restartVoting(): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        // Alle Votes für dieses Issue löschen
        Vote::query()
            ->where('issue_id', $this->currentIssue->id)
            ->delete();

        // State zurücksetzen
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];
        $this->myVote = null;

        // Event broadcasten (HideVotes signalisiert "neu starten")
        broadcast(new \App\Events\HideVotes($this->session->invite_code))->toOthers();
    }

    /**
     * Bestätigt die Schätzung und schließt das Issue ab (nur Owner).
     */
    public function confirmEstimate(int $storypoints): void
    {
        if (Auth::id() !== $this->session->owner_id || !$this->currentIssue) {
            return;
        }

        // Storypoints setzen und Issue abschließen
        $this->currentIssue->storypoints = $storypoints;
        $this->currentIssue->status = IssueStatus::FINISHED;
        $this->currentIssue->save();

        // State zurücksetzen
        $this->currentIssue = null;
        $this->votesRevealed = false;
        $this->votedUserIds = [];
        $this->votesByUser = [];
        $this->myVote = null;

        // Event an andere Teilnehmer broadcasten (Voting beendet)
        broadcast(new \App\Events\IssueCanceled($this->session->invite_code))->toOthers();
    }

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

        // Höchste Position ermitteln
        $maxPosition = Issue::query()
            ->where('session_id', $this->session->id)
            ->max('position') ?? -1;

        // Issue erstellen
        Issue::create([
            'title' => $this->newIssueTitle,
            'description' => $this->newIssueDescription ?: null,
            'session_id' => $this->session->id,
            'status' => IssueStatus::NEW,
            'position' => $maxPosition + 1,
            'jira_key' => $this->newIssueJiraKey ?: null,
            'jira_url' => $this->newIssueJiraUrl ?: null,
        ]);

        // Form zurücksetzen
        $this->newIssueTitle = '';
        $this->newIssueDescription = '';
        $this->newIssueJiraKey = '';
        $this->newIssueJiraUrl = '';
        $this->drawerOpen = false;

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\IssueAdded($this->session->invite_code))->toOthers();
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

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\IssueDeleted($this->session->invite_code))->toOthers();
    }

    // ===== Jira Import Methods =====

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
     *
     * @param bool $forceRefresh Wenn true, wird der Session-Cache ignoriert
     */
    public function loadJiraFilters(bool $forceRefresh = false): void
    {
        if (!$this->hasJiraCredentials()) {
            return;
        }

        // Bereits geladen und kein Force-Refresh → nichts tun
        if ($this->jiraFiltersLoaded && !$forceRefresh) {
            return;
        }

        $this->jiraLoading = true;
        $this->jiraError = '';

        try {
            $jiraService = new JiraService(Auth::user());
            $this->jiraFilters = $jiraService->getFavoriteFilters();
            $this->jiraFiltersLoaded = true;

            // In Session cachen für Page-Refreshes
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
     * Lädt Tickets per JQL und mapped sie.
     */
    private function loadTicketsFromJql(string $jql, JiraService $jiraService): void
    {
        $issues = $jiraService->searchByJql($jql);
        $this->mapIssuesToTickets($issues, $jiraService);
    }

    /**
     * Mapped Jira-Issues zu Ticket-Array für die Auswahl.
     *
     * @param array<object> $issues
     */
    private function mapIssuesToTickets(array $issues, JiraService $jiraService): void
    {
        $this->jiraTickets = [];

        foreach ($issues as $issue) {
            $mapped = $jiraService->mapJiraIssueToArray($issue);

            // Prüfen ob bereits in dieser Session importiert
            $alreadyImported = Issue::query()
                ->where('jira_key', $mapped['jira_key'])
                ->where('session_id', $this->session->id)
                ->exists();

            $this->jiraTickets[] = [
                'key' => $mapped['jira_key'],
                'title' => $mapped['title'],
                'description' => $mapped['description'],
                'url' => $mapped['jira_url'],
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
            ]);

            $ticket['alreadyImported'] = true;
            $importedCount++;
        }

        $this->selectedJiraTickets = [];
        $this->jiraSuccess = "{$importedCount} Ticket(s) importiert.";
        $this->jiraLoading = false;

        // Event broadcasten
        broadcast(new \App\Events\IssueAdded($this->session->invite_code))->toOthers();
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
     * Startet das Voting für ein Issue (nur Owner).
     */
    public function startVoting(int $issueId): void
    {
        // Nur Owner darf Voting starten
        if (Auth::id() !== $this->session->owner_id) {
            return;
        }

        // Alle laufenden Votings beenden
        Issue::query()
            ->where('session_id', $this->session->id)
            ->where('status', IssueStatus::VOTING)
            ->update(['status' => IssueStatus::NEW]);

        // Neues Voting starten
        $issue = Issue::find($issueId);
        if (!$issue || $issue->session_id !== $this->session->id) {
            return;
        }

        $issue->status = IssueStatus::VOTING;
        $issue->save();

        // State aktualisieren
        $this->currentIssue = $issue;
        $this->votesRevealed = false;
        $this->loadVotedUsers(); // Lädt existierende async Votes aus der DB

        // Event an andere Teilnehmer broadcasten
        broadcast(new IssueSelected($this->session->invite_code))->toOthers();
    }

    // ===== Voter Actions =====

    /**
     * Gibt einen Vote ab (für Teilnehmer).
     */
    public function submitVote(int $card): void
    {
        if (!$this->currentIssue || !Auth::check()) {
            return;
        }

        Vote::query()->updateOrCreate([
            'user_id' => Auth::id(),
            'issue_id' => $this->currentIssue->id,
        ], [
            'value' => $card,
        ]);

        $this->myVote = $card;
        $this->loadVotedUsers();

        // Event broadcasten
        broadcast(new \App\Events\AddVote($this->session->invite_code, Auth::user()))->toOthers();
    }

    /**
     * Entfernt den eigenen Vote (für Teilnehmer).
     */
    public function removeVote(): void
    {
        if (!$this->currentIssue || !Auth::check()) {
            return;
        }

        Vote::query()
            ->where('user_id', Auth::id())
            ->where('issue_id', $this->currentIssue->id)
            ->delete();

        $this->myVote = null;
        $this->loadVotedUsers();

        // Event broadcasten
        broadcast(new \App\Events\AddVote($this->session->invite_code, Auth::user()))->toOthers();
    }

    // ===== Helper Methods =====

    private function loadCurrentIssue(): void
    {
        $this->currentIssue = Issue::query()
            ->where('session_id', $this->session->id)
            ->where('status', 'voting')
            ->first();

        $this->loadVotedUsers();
    }

    private function loadVotedUsers(): void
    {
        if (!$this->currentIssue) {
            $this->votedUserIds = [];
            $this->votesByUser = [];
            $this->myVote = null;

            return;
        }

        $votes = Vote::query()
            ->where('issue_id', $this->currentIssue->id)
            ->get(['user_id', 'value']);

        $this->votedUserIds = $votes->pluck('user_id')->all();
        $this->votesByUser = $votes->pluck('value', 'user_id')->all();

        // Eigenen Vote laden
        $this->myVote = $this->votesByUser[Auth::id()] ?? null;
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

        // Event an andere Teilnehmer broadcasten
        broadcast(new \App\Events\IssueOrderChanged($this->session->invite_code))->toOthers();
    }

    public function render(): View
    {
        // Issues neu laden für aktuelle Daten
        $this->session->load('issues');

        // Issues gruppieren und nach Position sortieren
        $openIssues = $this->session->issues
            ->where('status', '!=', 'finished')
            ->where('status', '!=', 'voting')
            ->sortBy('position');

        $finishedIssues = $this->session->issues
            ->where('status', 'finished')
            ->sortBy('position');

        return view('livewire.v2.session-page', [
            'isOwner' => Auth::id() === $this->session->owner_id,
            'issueCount' => $this->session->issues->count(),
            'finishedCount' => $finishedIssues->count(),
            'participantCount' => $this->session->users->count(),
            'onlineCount' => count($this->onlineUserIds),
            'openIssues' => $openIssues,
            'finishedIssues' => $finishedIssues,
        ]);
    }
}
