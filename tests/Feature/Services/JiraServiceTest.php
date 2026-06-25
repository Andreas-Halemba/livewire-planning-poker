<?php

use App\Models\User;
use App\Services\JiraService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createJiraService(): JiraService
{
    $user = User::factory()->create([
        'jira_url' => 'https://jira.example.com',
        'jira_user' => 'user@example.com',
        'jira_api_key' => 'token',
    ]);

    return new JiraService($user);
}

test('mapJiraIssueToArray extracts parent issue data', function () {
    $service = createJiraService();

    $jiraIssue = (object) [
        'key' => 'ABC-123',
        'self' => 'https://jira.example.com/rest/api/2/issue/ABC-123',
        'fields' => (object) [
            'summary' => 'Story title',
            'issuetype' => (object) ['name' => 'Story'],
            'parent' => (object) [
                'key' => 'ABC-100',
                'self' => 'https://jira.example.com/rest/api/2/issue/ABC-100',
                'fields' => (object) [
                    'summary' => 'Epic title',
                ],
            ],
        ],
        'renderedFields' => [],
    ];

    $mapped = $service->mapJiraIssueToArray($jiraIssue);

    expect($mapped)->toMatchArray([
        'title' => 'Story title',
        'jira_key' => 'ABC-123',
        'jira_url' => 'https://jira.example.com/browse/ABC-123',
        'issue_type' => 'Story',
        'parent_key' => 'ABC-100',
        'parent_title' => 'Epic title',
        'parent_url' => 'https://jira.example.com/browse/ABC-100',
        'estimate_unit' => 'sp',
    ]);
});

test('mapJiraIssueToArray returns null parent data when no parent exists', function () {
    $service = createJiraService();

    $jiraIssue = (object) [
        'key' => 'ABC-123',
        'self' => 'https://jira.example.com/rest/api/2/issue/ABC-123',
        'fields' => (object) [
            'summary' => 'Story title',
            'issuetype' => (object) ['name' => 'Story'],
        ],
        'renderedFields' => [],
    ];

    $mapped = $service->mapJiraIssueToArray($jiraIssue);

    expect($mapped['parent_key'])->toBeNull();
    expect($mapped['parent_title'])->toBeNull();
    expect($mapped['parent_url'])->toBeNull();
});

test('mapJiraIssueToArray handles parent without summary', function () {
    $service = createJiraService();

    $jiraIssue = (object) [
        'key' => 'ABC-123',
        'self' => 'https://jira.example.com/rest/api/2/issue/ABC-123',
        'fields' => (object) [
            'summary' => 'Story title',
            'issuetype' => (object) ['name' => 'Story'],
            'parent' => (object) [
                'key' => 'ABC-100',
                'self' => 'https://jira.example.com/rest/api/2/issue/ABC-100',
                'fields' => (object) [],
            ],
        ],
        'renderedFields' => [],
    ];

    $mapped = $service->mapJiraIssueToArray($jiraIssue);

    expect($mapped['parent_key'])->toBe('ABC-100');
    expect($mapped['parent_title'])->toBeNull();
    expect($mapped['parent_url'])->toBe('https://jira.example.com/browse/ABC-100');
});
