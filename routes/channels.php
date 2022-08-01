<?php

use App\Models\Display;
use App\Models\Raspberry;
use App\Models\Store;
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

Broadcast::channel('App.Models.Raspberry.{raspberry}',
    function (Raspberry $authRaspberry, Raspberry $raspberry) {
        return $raspberry->id === $authRaspberry->id;
    });

Broadcast::channel('App.Models.Display.{display}',
    function (Store $authStore, Display $display) {
        return $display->store->id === $authStore->id;
    });
