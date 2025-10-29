<?php

namespace App\Services;

use App\Models\User;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;
use Illuminate\Support\Facades\Log;

class JiraService
{
    private ?IssueService $issueService = null;
    private ?User $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->initializeIssueService();
    }

    private function initializeIssueService(): void
    {
        // Use user-specific credentials
        $config = new \JiraRestApi\Configuration\ArrayConfiguration([
            'jiraHost' => $this->user->jira_url,
            'jiraUser' => $this->user->jira_user,
            'jiraPassword' => $this->user->jira_api_key,
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
        if (!$this->issueService) {
            throw new \RuntimeException('Jira service not initialized. Please configure your Jira credentials in your profile.');
        }

        $jql = "project = \"{$projectKey}\" AND status = \"{$status}\" ORDER BY created DESC";

        try {
            $response = $this->issueService->search(jql: $jql, expand: 'renderedFields');
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

        ray($jiraIssue->fields);

        return [
            'title' => $jiraIssue->fields->summary ?? 'No title',
            'description' => $jiraIssue->renderedFields['description'] ?? null,
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
     * Test Jira connection by fetching current user info
     *
     * @return array{success: bool, username?: string}
     */
    public function testConnection(): array
    {
        if (!$this->issueService) {
            return ['success' => false];
        }

        try {
            // Use the REST API directly to get current user info via /myself endpoint
            // This is simpler and doesn't require a JQL query
            $url = $this->user->jira_url . '/rest/api/2/myself';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_USERPWD, $this->user->jira_user . ':' . $this->user->jira_api_key);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $userData = json_decode($response, true);
                return [
                    'success' => true,
                    'username' => $userData['displayName'] ?? $userData['name'] ?? '',
                ];
            }

            return ['success' => false];
        } catch (\Exception $e) {
            Log::error('Jira connection test failed: ' . $e->getMessage());
            return ['success' => false];
        }
    }
}
