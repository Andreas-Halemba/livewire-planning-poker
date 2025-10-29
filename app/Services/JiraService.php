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
        $issueKey = $jiraIssue->key ?? '';
        $browserUrl = $this->convertApiUrlToBrowserUrl($jiraIssue->self ?? '', $issueKey);

        return [
            'title' => $jiraIssue->fields->summary ?? 'No title',
            'description' => $jiraIssue->fields->description ?? null,
            'jira_key' => $issueKey,
            'jira_url' => $browserUrl,
        ];
    }

    /**
     * Convert Jira API URL to browser URL
     *
     * @param string $apiUrl
     * @param string $issueKey
     * @return string
     */
    private function convertApiUrlToBrowserUrl(string $apiUrl, string $issueKey): string
    {
        if (empty($apiUrl) || empty($issueKey)) {
            return '';
        }

        // Extract base URL from API URL
        // Example: https://jira.example.com/rest/api/2/issue/12345
        // Should become: https://jira.example.com/browse/PROJECT-123

        $baseUrl = preg_replace('#/rest/api/.*#', '', $apiUrl);

        return $baseUrl . '/browse/' . $issueKey;
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
