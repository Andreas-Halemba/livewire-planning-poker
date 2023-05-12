<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_id',
        'invite_code',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function issues()
    {
        return $this->hasMany(Issue::class, 'session_id', 'id');
    }

    // Session model
    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
