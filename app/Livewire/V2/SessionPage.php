<?php

declare(strict_types=1);

namespace App\Livewire\V2;

use App\Livewire\V2\Traits\HandlesIssues;
use App\Livewire\V2\Traits\HandlesJiraImport;
use App\Livewire\V2\Traits\HandlesPresence;
use App\Livewire\V2\Traits\HandlesVoting;
use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * V2 SessionPage - Hauptkomponente für die Voting-Session.
 *
 * Diese Komponente orchestriert alle Session-Funktionen:
 * - Präsenz-Status (online/offline via Presence Channel)
 * - Voting-System (start, reveal, hide, cancel, confirm)
 * - Issue-Management (CRUD, Drag & Drop)
 * - Jira-Import (Filter, URL, Keys)
 */
class SessionPage extends Component
{
    use HandlesPresence;
    use HandlesVoting;
    use HandlesIssues;
    use HandlesJiraImport;

    public Session $session;

    public function mount(string $inviteCode): void
    {
        $this->session = Session::with(['issues', 'users', 'owner'])
            ->where('invite_code', $inviteCode)
            ->firstOrFail();

        // Trait-Initialisierungen
        $this->initializePresence();
        $this->loadCurrentIssue();
        $this->initializeJiraFilters();
    }

    /**
     * Wechselt den Tab im Drawer.
     * Explizite Methode für besseres wire:target Handling.
     */
    public function switchTab(string $tab): void
    {
        $this->drawerTab = $tab;

        if ($tab === 'jira') {
            $this->onJiraTabOpened();
        }
    }

    /**
     * Kombiniert alle Event Listeners aus den Traits.
     *
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return array_merge(
            $this->getPresenceListeners(),
            $this->getVotingListeners(),
            $this->getIssueListeners(),
        );
    }

    public function render(): View
    {
        $this->session->load('issues');

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
