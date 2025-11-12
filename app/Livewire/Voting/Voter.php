<?php

namespace App\Livewire\Voting;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Inspector\Laravel\InspectorLivewire;
use Livewire\Component;

class Voter extends Component
{
    use InspectorLivewire;

    public Session $session;

    public function render(): View
    {
        // Ensure issues are loaded for the view
        if (!$this->session->relationLoaded('issues')) {
            $this->session->load('issues');
        }

        return view('livewire.voting.voter', [
            'session' => $this->session,
        ]);
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueSelected" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.IssueCanceled" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueEvent',
            "echo-presence:session.{$this->session->invite_code},.AddVote" => 'handleVoteEvent',
        ];
    }

    public function handleIssueEvent(): void
    {
        // Reload session with issues to ensure fresh data
        $this->session->load('issues');
    }

    public function handleVoteEvent(): void
    {
        // Reload session with issues and votes
        $this->session->load(['issues.votes']);
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
