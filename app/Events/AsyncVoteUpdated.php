<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast-only Event for async voting progress.
 *
 * Intentionally does NOT include vote values (story points) to avoid leaking estimates.
 */
class AsyncVoteUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        private string $sessionCode,
        public int $issueId,
        public int $userId,
        public bool $hasVote,
    ) {}

    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('session.' . $this->sessionCode);
    }

    public function broadcastAs(): string
    {
        return 'AsyncVoteUpdated';
    }
}
