<?php

namespace App\Livewire;

use App\Models\Session;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class VotingIssueList extends Component
{
    /** @var Collection<int, \App\Models\Issue> */
    public Collection $issues;

    public Session $session;

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},.IssueAdded" => 'handleIssueChange',
            "echo-presence:session.{$this->session->invite_code},.IssueDeleted" => 'handleIssueChange',
        ];
    }

    public function handleIssueChange(): void
    {
        // Reload issues collection without full refresh
        $this->issues = $this->session->issues()->get();
    }

    public function render(): View
    {
        return view('livewire.voting-issue-list');
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
