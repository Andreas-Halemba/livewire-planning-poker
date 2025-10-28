<?php

namespace App\Services;

use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use Illuminate\Support\Facades\Log;

class JiraService
{
    private IssueService $issueService;

    public function __construct()
    {
        $config = new \JiraRestApi\Configuration\ArrayConfiguration([
            'jiraHost' => config('jira.host'),
            'jiraUser' => config('jira.user'),
            'jiraPassword' => config('jira.password'),
        ]);

        $this->issueService = new IssueService($config);
    }

    /**
     * Search for Jira issues by project key and status
     *
     * @param string $projectKey
     * @param string $status
     * @return array<int, object>
     * @throws JiraException
     */
    public function searchIssuesByProjectAndStatus(string $projectKey, string $status): array
    {
        $jql = "project = \"{$projectKey}\" AND status = \"{$status}\" ORDER BY created DESC";

        try {
            $response = $this->issueService->search($jql);
            return $response->getIssues();
        } catch (JiraException $e) {
            Log::error('Jira API error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Map Jira issue to array format for Issue model
     *
     * @param object $jiraIssue
     * @return array<string, string|null>
     */
    public function mapJiraIssueToArray(object $jiraIssue): array
    {
        return [
            'title' => $jiraIssue->fields->summary ?? 'No title',
            'description' => $jiraIssue->fields->description ?? null,
            'jira_key' => $jiraIssue->key ?? '',
            'jira_url' => $jiraIssue->self ?? '',
        ];
    }

    /**
     * Test Jira connection
     *
     * @return bool
     */
    public function testConnection(): bool
    {
        try {
            $this->issueService->search('ORDER BY created DESC');
            return true;
        } catch (JiraException $e) {
            Log::error('Jira connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
