<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('tontine.{tontineId}', function (User $user, $tontineId) {
    if ((int) $user->role_id === User::ROLE_ADMIN) {
        return true;
    }

    return $user->tontines()->where('tontines.id', $tontineId)->exists();
});
