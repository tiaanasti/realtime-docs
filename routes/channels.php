<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel(
    'document.{id}',
    function ($user, $id)
    {
        return [

            'id' => $user->id,

            'name' => $user->name,

        ];
    }
);


Broadcast::channel(
    'documents',
    function ($user)
    {
        return true;
    }
);