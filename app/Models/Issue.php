<?php

namespace App\Models;

use Database\Factories\IssueFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public const STATUS_NEW = 'open';

    public const STATUS_VOTING = 'voting';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'title',
        'description',
        'session_id',
        'status',
        'storypoints',
        'jira_key',
        'jira_url',
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
        // If we have Jira URL, create link using Jira key
        if ($this->jira_url && $this->jira_key) {
            return "<a href='{$this->jira_url}' class='hover:underline' target='_blank'>{$this->jira_key}</a>";
        }

        // Fallback to existing URL parsing logic
        $pattern = '/SAN-\d+/';
        if (filter_var($this->title, FILTER_VALIDATE_URL) && preg_match($pattern, $this->title, $matches)) {
            return "<a href='{$this->title}' class='hover:underline' target='_blank'>{$matches[0]}</a>";
        }

        return $this->title;
    }

    // function that returns true if the status is voiting
    public function isVoting(): bool
    {
        return $this->status === self::STATUS_VOTING;
    }

    // function that returns true if the status is finished
    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }
}
