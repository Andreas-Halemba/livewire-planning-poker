<?php

declare(strict_types=1);

namespace App\Actions\Jira;

use App\Models\Issue;
use App\Models\User;
use App\Services\JiraService;
use Illuminate\Support\Facades\Log;

/**
 * Sync confirmed story points back to Jira (best-effort).
 *
 * - Only runs if the owner has Jira credentials configured
 * - Only runs if the Issue has a Jira key or a Jira link containing an issue key
 * - Never throws (errors are logged)
 */
class SyncStoryPointsToJira
{
    public function sync(User $owner, Issue $issue): void
    {
        $storypoints = $issue->storypoints;
        if ($storypoints === null) {
            return;
        }

        if (!$this->ownerHasJiraCredentials($owner)) {
            return;
        }

        $issueKey = $this->resolveIssueKey($issue);
        if ($issueKey === null) {
            return;
        }

        try {
            $jiraService = new JiraService($owner);
            $success = $jiraService->updateStoryPoints($issueKey, $storypoints);

            if (!$success) {
                Log::warning("Jira story points update returned false for {$issueKey}");
            }
        } catch (\Throwable $e) {
            // Best-effort only: do not break the user flow if Jira update fails
            Log::error("Failed to update Jira story points for {$issueKey}: {$e->getMessage()}");
        }
    }

    private function ownerHasJiraCredentials(User $owner): bool
    {
        return (bool) ($owner->jira_url && $owner->jira_user && $owner->jira_api_key);
    }

    private function resolveIssueKey(Issue $issue): ?string
    {
        if (!empty($issue->jira_key)) {
            return strtoupper(trim($issue->jira_key));
        }

        $url = $issue->jira_url;
        if (empty($url)) {
            return null;
        }

        // Extract KEY-123 from any Jira URL (browse link, REST link, etc.)
        if (preg_match('/([A-Z][A-Z0-9]+-\d+)/i', $url, $matches) === 1) {
            return strtoupper($matches[1]);
        }

        return null;
    }
}
