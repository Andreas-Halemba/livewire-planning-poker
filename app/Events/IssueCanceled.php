<?php

namespace App\Events;

use App\Models\Issue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueCanceled implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Issue $issue)
    {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('session.'.$this->issue->session->invite_code);
    }

    public function broadcastAs(): string
    {
        return 'IssueCanceled';
    }
}
