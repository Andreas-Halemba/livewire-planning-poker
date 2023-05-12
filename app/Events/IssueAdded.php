<?php

namespace App\Events;

use App\Models\Issue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueAdded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Issue $issue)
    {
    }

    public function broadcastOn()
    {
        return new PresenceChannel('session.'.$this->issue->session->invite_code);
    }

    public function broadcastAs()
    {
        return 'IssueAdded';
    }
}
