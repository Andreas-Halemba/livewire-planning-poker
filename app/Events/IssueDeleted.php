<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IssueDeleted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(private string $sessionCode)
    {
    }

    public function broadcastOn()
    {
        return new Channel('session.'.$this->sessionCode);
    }

    public function broadcastAs()
    {
        return 'IssueDeleted';
    }
}
