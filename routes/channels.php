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
    if (Session::whereInviteCode($invite_code)->firstOrFail()) {
        return ['id' => $user->id, 'name' => $user->name];
    }
});
