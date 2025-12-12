<?php

declare(strict_types=1);

namespace App\Livewire\V2;

use App\Livewire\V2\Traits\HandlesIssues;
use App\Livewire\V2\Traits\HandlesJiraImport;
use App\Livewire\V2\Traits\HandlesPresence;
use App\Livewire\V2\Traits\HandlesVoting;
use App\Models\Session;
use App\Services\SessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector as LivewireRedirector;

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

    public function mount(string $inviteCode): RedirectResponse|LivewireRedirector|null
    {
        $this->session = Session::with(['issues', 'users', 'owner'])
            ->where('invite_code', $inviteCode)
            ->firstOrFail();

        // Archived sessions are read-only and should be shown in archived view
        if ($this->session->archived_at !== null) {
            return Redirect::route('session.archived', $inviteCode);
        }

        // Ensure the current user joins the session in the DB on first visit
        if (Auth::check()) {
            app(SessionService::class)->joinSession($this->session);
            $this->session->load('users');
        }

        // Trait-Initialisierungen
        $this->initializePresence();
        $this->loadCurrentIssue();
        $this->initializeJiraFilters();

        return null;
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
