<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AddVote implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(private string $sessionCode, public User $user) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('session.' . $this->sessionCode);
    }

    public function broadcastAs(): string
    {
        return 'AddVote';
    }
}
