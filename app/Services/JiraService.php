<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;

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
            $response = $this->issueService->search(jql: $jql, maxResults: 100, expand: 'renderedFields');
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
     * Update story points for a Jira issue
     *
     * @param string $issueKey The Jira issue key (e.g., "PROJECT-123")
     * @param int $storyPoints The story points value to set
     * @return bool True if successful, false otherwise
     */
    public function updateStoryPoints(string $issueKey, int $storyPoints): bool
    {
        if (!$this->issueService) {
            Log::error('Jira service not initialized for updating story points');
            return false;
        }

        try {
            // First, get the issue to find the custom field ID for story points
            $issue = $this->issueService->get($issueKey);

            // Try to find the story points field
            // Story points can be stored in different fields depending on Jira configuration:
            // - customfield_10002 (common default)
            // - customfield_10004 (alternative)
            // - storyPoints (if using Jira Software)
            $storyPointsField = null;

            // Check common story points field names
            $possibleFields = ['customfield_10002', 'customfield_10004', 'storyPoints'];

            foreach ($possibleFields as $fieldName) {
                if (isset($issue->fields->{$fieldName})) {
                    $storyPointsField = $fieldName;
                    break;
                }
            }

            // If no standard field found, try to use REST API to find it
            if (!$storyPointsField) {
                return $this->updateStoryPointsViaRestApi($issueKey, $storyPoints);
            }

            // Update using IssueService with IssueField
            $issueField = new \JiraRestApi\Issue\IssueField(true);

            // Use addCustomField if it's a custom field, otherwise set directly
            if (str_starts_with($storyPointsField, 'customfield_')) {
                $issueField->addCustomField($storyPointsField, $storyPoints);
            } else {
                // For standard fields, we need to set them directly
                // Since storyPoints might be a standard field in some Jira configurations
                $issueField->customFields = [$storyPointsField => $storyPoints];
            }

            $this->issueService->update($issueKey, $issueField);

            Log::info("Successfully updated story points for {$issueKey} to {$storyPoints}");
            return true;

        } catch (JiraException $e) {
            Log::error("Failed to update story points for {$issueKey}: " . $e->getMessage());
            // Try REST API as fallback
            return $this->updateStoryPointsViaRestApi($issueKey, $storyPoints);
        } catch (\Exception $e) {
            Log::error("Error updating story points for {$issueKey}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update story points using REST API directly (fallback method)
     */
    private function updateStoryPointsViaRestApi(string $issueKey, int $storyPoints): bool
    {
        try {
            // Use REST API to update story points
            // First, get issue metadata to find story points field
            $metadataUrl = $this->user->jira_url . '/rest/api/2/issue/' . $issueKey . '/editmeta';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $metadataUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_USERPWD, $this->user->jira_user . ':' . $this->user->jira_api_key);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error("Failed to get issue metadata for {$issueKey}");
                return false;
            }

            $metadata = json_decode($response, true);
            $storyPointsField = null;

            // Find story points field in metadata
            foreach ($metadata['fields'] ?? [] as $fieldId => $field) {
                if (isset($field['name']) && (
                    stripos($field['name'], 'story point') !== false
                    || $fieldId === 'customfield_10002'
                    || $fieldId === 'customfield_10004'
                )) {
                    $storyPointsField = $fieldId;
                    break;
                }
            }

            if (!$storyPointsField) {
                Log::warning("Story points field not found for issue {$issueKey}");
                return false;
            }

            // Update the issue
            $updateUrl = $this->user->jira_url . '/rest/api/2/issue/' . $issueKey;
            $updateData = json_encode([
                'fields' => [
                    $storyPointsField => $storyPoints,
                ],
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $updateUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $updateData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_USERPWD, $this->user->jira_user . ':' . $this->user->jira_api_key);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 204 || $httpCode === 200) {
                Log::info("Successfully updated story points for {$issueKey} to {$storyPoints} via REST API");
                return true;
            }

            Log::error("Failed to update story points for {$issueKey}: HTTP {$httpCode}");
            return false;

        } catch (\Exception $e) {
            Log::error("Error updating story points via REST API for {$issueKey}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's favorite Jira filters
     *
     * @return array<int, array{id: string, name: string, jql: string}>
     */
    public function getFavoriteFilters(): array
    {
        $url = $this->user->jira_url . '/rest/api/2/filter/favourite';

        $response = $this->makeApiRequest($url);

        if ($response === null) {
            return [];
        }

        $filters = [];
        foreach ($response as $filter) {
            $filters[] = [
                'id' => (string) ($filter['id'] ?? ''),
                'name' => $filter['name'] ?? 'Unnamed Filter',
                'jql' => $filter['jql'] ?? '',
            ];
        }

        return $filters;
    }

    /**
     * Search issues by JQL query
     *
     * @param string $jql
     * @param int $maxResults
     * @return array<int, object>
     * @throws JiraException
     */
    public function searchByJql(string $jql, int $maxResults = 100): array
    {
        if (!$this->issueService) {
            throw new \RuntimeException('Jira service not initialized.');
        }

        try {
            $response = $this->issueService->search(
                jql: $jql,
                maxResults: $maxResults,
                expand: 'renderedFields',
            );
            return $response->getIssues();
        } catch (JiraException $e) {
            Log::error('Jira JQL search error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get multiple issues by their keys
     *
     * @param array<string> $keys
     * @return array<int, object>
     */
    public function getIssuesByKeys(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        // Build JQL: key in (KEY-1, KEY-2, ...)
        $escapedKeys = array_map(fn($k) => '"' . trim($k) . '"', $keys);
        $jql = 'key in (' . implode(', ', $escapedKeys) . ')';

        try {
            return $this->searchByJql($jql, count($keys));
        } catch (JiraException $e) {
            Log::error('Failed to get issues by keys: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Parse user input to extract JQL, filter ID, or issue keys
     *
     * @param string $input URL, filter ID, or comma-separated issue keys
     * @return array{type: string, value: string|array<string>}
     */
    public function parseJiraInput(string $input): array
    {
        $input = trim($input);

        // Check for filter URL: .../issues/?filter=12345 or .../filters/12345
        if (preg_match('/filter[=\/](\d+)/', $input, $matches)) {
            return ['type' => 'filter', 'value' => $matches[1]];
        }

        // Check for JQL in URL: .../issues/?jql=...
        if (preg_match('/jql=([^&]+)/', $input, $matches)) {
            return ['type' => 'jql', 'value' => urldecode($matches[1])];
        }

        // Check for issue keys pattern (PROJECT-123)
        if (preg_match_all('/([A-Z][A-Z0-9]+-\d+)/', strtoupper($input), $matches)) {
            return ['type' => 'keys', 'value' => array_unique($matches[1])];
        }

        // Fallback: treat as JQL if it looks like one
        if (str_contains($input, '=') || str_contains($input, ' AND ') || str_contains($input, ' OR ')) {
            return ['type' => 'jql', 'value' => $input];
        }

        return ['type' => 'unknown', 'value' => $input];
    }

    /**
     * Get JQL from a filter ID
     *
     * @param string $filterId
     * @return string|null
     */
    public function getFilterJql(string $filterId): ?string
    {
        $url = $this->user->jira_url . '/rest/api/2/filter/' . $filterId;

        $response = $this->makeApiRequest($url);

        return $response['jql'] ?? null;
    }

    /**
     * Make a generic API request to Jira
     *
     * @param string $url
     * @return array<mixed>|null
     */
    private function makeApiRequest(string $url): ?array
    {
        try {
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
                return json_decode($response, true);
            }

            Log::warning("Jira API request failed: HTTP {$httpCode} for {$url}");
            return null;
        } catch (\Exception $e) {
            Log::error('Jira API request error: ' . $e->getMessage());
            return null;
        }
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
