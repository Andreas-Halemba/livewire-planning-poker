<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
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
    ];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function getAverageVoteAttribute()
    {
        return round($this->votes()->avg('value'), 1);
    }

    public function getTitleHtmlAttribute()
    {
        $pattern = '/SAN-\d+/';

        if (filter_var($this->title, FILTER_VALIDATE_URL) && preg_match($pattern, $this->title, $matches)) {
            return "<a href='{$this->title}' class='hover:underline' target='_blank'>{$matches[0]}</a>";
        } else {
            return $this->title;
        }
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
