<?php

declare(strict_types=1);

namespace App\Livewire\V2\Traits;

/**
 * Trait für Presence Channel Handling.
 *
 * Verwaltet den Online-Status der Session-Teilnehmer.
 */
trait HandlesPresence
{
    /** @var array<int> IDs der online User */
    public array $onlineUserIds = [];

    /**
     * Initialisiert die Presence-Daten.
     */
    protected function initializePresence(): void
    {
        // Aktueller User ist immer online
        if (auth()->check()) {
            $this->onlineUserIds = [auth()->id()];
        }
    }

    /**
     * Gibt die Presence Event Listeners zurück.
     *
     * @return array<string, string>
     */
    protected function getPresenceListeners(): array
    {
        return [
            "echo-presence:session.{$this->session->invite_code},here" => 'handleUsersHere',
            "echo-presence:session.{$this->session->invite_code},joining" => 'handleUserJoining',
            "echo-presence:session.{$this->session->invite_code},leaving" => 'handleUserLeaving',
        ];
    }

    /**
     * Handler: Alle aktuell online User.
     *
     * @param array<int, array{id: int}> $users
     */
    public function handleUsersHere(array $users): void
    {
        $this->onlineUserIds = collect($users)
            ->pluck('id')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Handler: Ein User ist beigetreten.
     *
     * @param array{id: int} $user
     */
    public function handleUserJoining(array $user): void
    {
        if (isset($user['id']) && !in_array($user['id'], $this->onlineUserIds)) {
            $this->onlineUserIds[] = $user['id'];
        }
    }

    /**
     * Handler: Ein User hat verlassen.
     *
     * @param array{id: int} $user
     */
    public function handleUserLeaving(array $user): void
    {
        if (isset($user['id'])) {
            $this->onlineUserIds = array_values(
                array_filter($this->onlineUserIds, fn($id) => $id !== $user['id']),
            );
        }
    }
}
