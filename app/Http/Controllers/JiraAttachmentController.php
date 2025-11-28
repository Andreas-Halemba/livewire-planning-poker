<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class JiraAttachmentController extends Controller
{
    /**
     * Proxy Jira attachment images with authentication
     *
     * @param Request $request
     * @param int $attachmentId
     * @return Response
     */
    public function proxy(Request $request, int $attachmentId): Response
    {
        // Get issue ID from query parameter
        $issueId = $request->query('issue_id');

        if (!$issueId) {
            return response('Issue ID is required', 400);
        }

        $issue = Issue::find($issueId);

        if (!$issue) {
            return response('Issue not found', 404);
        }

        // Get session owner's credentials (they imported the issue)
        $session = $issue->session;
        $owner = $session->owner;

        if (!$owner->jira_url || !$owner->jira_user || !$owner->jira_api_key) {
            return response('Jira credentials not configured', 403);
        }

        // Build the Jira API URL for the attachment
        $baseUrl = $this->getJiraBaseUrl($issue->jira_url);

        if (!$baseUrl) {
            return response('Jira URL not configured for this issue', 400);
        }

        $attachmentUrl = rtrim($baseUrl, '/') . "/rest/api/3/attachment/content/{$attachmentId}";

        try {
            // Fetch the image from Jira API with authentication
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $attachmentUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERPWD, $owner->jira_user . ':' . $owner->jira_api_key);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HEADER, true);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::warning("Failed to fetch Jira attachment: HTTP {$httpCode} for {$attachmentUrl}");
                return response('Failed to fetch attachment', $httpCode ?: 500);
            }

            // Split headers and body
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);

            // Extract content type from headers
            $contentType = 'image/png'; // default
            if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $matches)) {
                $contentType = trim($matches[1]);
            }

            // Return the image with proper headers
            return response($body, 200)
                ->header('Content-Type', $contentType)
                ->header('Cache-Control', 'public, max-age=3600');
        } catch (\Exception $e) {
            Log::error('Error proxying Jira attachment: ' . $e->getMessage());
            return response('Error fetching attachment', 500);
        }
    }

    /**
     * Extract base URL from jira_url
     */
    private function getJiraBaseUrl(?string $jiraUrl): ?string
    {
        if (empty($jiraUrl)) {
            return null;
        }

        // If it's a browser URL (contains /browse/), extract base
        if (str_contains($jiraUrl, '/browse/')) {
            return preg_replace('#/browse/.*#', '', $jiraUrl);
        }

        // If it's an API URL (contains /rest/api/), extract base
        if (str_contains($jiraUrl, '/rest/api/')) {
            return preg_replace('#/rest/api/.*#', '', $jiraUrl);
        }

        // Otherwise assume it's already a base URL
        return $jiraUrl;
    }
}
