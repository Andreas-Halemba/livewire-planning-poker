<?php

use App\Models\Session;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('session.{invite_code}', function (User $user, string $invite_code) {
    $session = Session::whereInviteCode($invite_code)->first();
    if (!$session) {
        return false;
    }

    // Allow owner to join the channel
    if ($session->owner_id === $user->id) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    // Allow regular users who are part of the session
    if ($session->users->contains($user)) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});
