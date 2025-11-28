<?php

namespace App\Models;

use App\Enums\IssueStatus;
use Database\Factories\IssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * App\Models\Issue
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $status
 * @property int $session_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $storypoints
 * @property string|null $jira_key
 * @property string|null $jira_url
 *
 * @property-read float $average_vote
 * @property-read string $title_html
 * @property-read \App\Models\Session $session
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Vote> $votes
 * @property-read int|null $votes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue query()
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereSessionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereStorypoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Issue whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Issue extends Model
{
    /** @use HasFactory<IssueFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'session_id',
        'status',
        'storypoints',
        'jira_key',
        'jira_url',
        'position',
    ];

    protected $casts = [
        'status' => IssueStatus::class,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /** @return HasMany<Vote, *> */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function getAverageVoteAttribute(): float
    {
        return round((int) $this->votes->avg('value'), 1);
    }

    public function getTitleHtmlAttribute(): string
    {
        // If we have Jira URL and key, create link
        if ($this->jira_url && $this->jira_key) {
            $browserUrl = $this->getJiraBrowserUrl();
            return "<a href='{$browserUrl}' class='hover:underline text-accent' target='_blank'>{$this->jira_key}<br>" . Str::limit($this->title, 100) . "</a>";
        }

        // Fallback to existing URL parsing logic
        $pattern = '/SAN-\d+/';
        if (filter_var($this->title, FILTER_VALIDATE_URL) && preg_match($pattern, $this->title, $matches)) {
            return "<a href='{$this->title}' class='hover:underline' target='_blank'>{$matches[0]}</a>";
        }

        return $this->title;
    }

    /**
     * Convert Jira API URL to browser URL if needed
     */
    public function getJiraBrowserUrl(): string
    {
        if (empty($this->jira_url) || empty($this->jira_key)) {
            return $this->jira_url ?? '';
        }

        // If it's already a browser URL (contains /browse/), return as is
        if (str_contains($this->jira_url, '/browse/')) {
            return $this->jira_url;
        }

        // If it's an API URL (contains /rest/api/), convert it
        if (str_contains($this->jira_url, '/rest/api/')) {
            $baseUrl = preg_replace('#/rest/api/.*#', '', $this->jira_url);
            return $baseUrl . '/browse/' . $this->jira_key;
        }

        // Fallback: assume it's a base URL and append /browse/key
        // This handles cases where only the base URL was stored
        return rtrim($this->jira_url, '/') . '/browse/' . $this->jira_key;
    }

    /**
     * Get formatted HTML description with converted attachment URLs
     */
    public function getFormattedDescriptionAttribute(): ?string
    {
        if (empty($this->description)) {
            return null;
        }

        $html = $this->description;

        // Replace Jira attachment URLs with proxy URLs
        // Pattern: /rest/api/3/attachment/content/641569 or https://jira.example.com/rest/api/3/attachment/content/641569
        $html = preg_replace_callback(
            '#(?:https?://[^/]+)?/rest/api/\d+/attachment/content/(\d+)#',
            function ($matches) {
                $attachmentId = $matches[1];
                $proxyUrl = route('jira.attachment.proxy', ['attachmentId' => $attachmentId]) . '?issue_id=' . $this->id;
                return $proxyUrl;
            },
            $html,
        );

        // Also handle attachment URLs in img src attributes
        $html = preg_replace_callback(
            '#src=["\']((?:https?://[^/]+)?/rest/api/\d+/attachment/content/(\d+))([^"\']*)["\']#',
            function ($matches) {
                $attachmentId = $matches[2];
                $proxyUrl = route('jira.attachment.proxy', ['attachmentId' => $attachmentId]) . '?issue_id=' . $this->id;
                return 'src="' . $proxyUrl . '"';
            },
            $html,
        );

        // Convert relative links to absolute URLs (for non-image attachments)
        $baseUrl = $this->getJiraBaseUrl();
        if ($baseUrl) {
            $html = preg_replace_callback(
                '#href=["\'](/secure/attachment/[^"\']+)["\']#',
                function ($matches) use ($baseUrl) {
                    return 'href="' . rtrim($baseUrl, '/') . $matches[1] . '"';
                },
                $html,
            );
        }

        return $html;
    }

    /**
     * Extract base URL from jira_url
     */
    private function getJiraBaseUrl(): ?string
    {
        if (empty($this->jira_url)) {
            return null;
        }

        // If it's a browser URL (contains /browse/), extract base
        if (str_contains($this->jira_url, '/browse/')) {
            return preg_replace('#/browse/.*#', '', $this->jira_url);
        }

        // If it's an API URL (contains /rest/api/), extract base
        if (str_contains($this->jira_url, '/rest/api/')) {
            return preg_replace('#/rest/api/.*#', '', $this->jira_url);
        }

        // Otherwise assume it's already a base URL
        return $this->jira_url;
    }

    // function that returns true if the status is voiting
    public function isVoting(): bool
    {
        return $this->status === IssueStatus::VOTING;
    }

    // function that returns true if the status is finished
    public function isFinished(): bool
    {
        return $this->status === IssueStatus::FINISHED;
    }
}
