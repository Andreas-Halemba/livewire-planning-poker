<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueDeleted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(private string $sessionCode) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('session.' . $this->sessionCode);
    }

    public function broadcastAs(): string
    {
        return 'IssueDeleted';
    }
}
