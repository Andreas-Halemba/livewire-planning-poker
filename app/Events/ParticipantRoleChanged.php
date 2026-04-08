<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A session member changed their voter/viewer role; other clients should reload membership.
 */
class ParticipantRoleChanged implements ShouldBroadcastNow
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
        return 'ParticipantRoleChanged';
    }
}
