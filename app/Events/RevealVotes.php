<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RevealVotes implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Session $session)
    {
    }

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('session.'.$this->session->invite_code);
    }

    public function broadcastAs(): string
    {
        return 'RevealVotes';
    }
}
