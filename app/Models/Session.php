<?php

namespace App\Models;

use App\Enums\IssueStatus;
use Database\Factories\SessionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Session
 *
 * @property int $id
 * @property string $name
 * @property string $invite_code
 * @property int $owner_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Issue> $issues
 * @property-read int|null $issues_count
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Session newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Session query()
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereInviteCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Session whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Session extends Model
{
    /** @use HasFactory<SessionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'invite_code',
        'archived_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class, 'session_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @param Builder<Session> $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    /**
     * @param Builder<Session> $query
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }

    public function currentIssue(): ?Issue
    {
        /** @var Issue|null */
        return $this->issues()->where('status', IssueStatus::VOTING)->first();
    }
}
