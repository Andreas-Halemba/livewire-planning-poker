<?php

namespace App\Livewire\Concerns;

use App\Models\Session;
use Illuminate\Support\Facades\Auth;

use function abort_if;

trait ManagesSessions
{
    /**
     * @return array<int, string>
     */
    public function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            'sessions-updated' => '$refresh',
        ]);
    }

    protected function dispatchSessionsUpdated(): void
    {
        $this->dispatch('sessions-updated');
    }

    protected function ownedSessionOrFail(string $sessionId): Session
    {
        return Session::whereOwnerId($this->ownerId())->findOrFail($sessionId);
    }

    protected function ownerId(): int
    {
        $ownerId = Auth::id();
        abort_if($ownerId === null, 403);

        return $ownerId;
    }
}
