<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | Define qual broadcaster será usado por padrão.
    |
    | Suportados: "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'ably'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Aqui ficam as conexões disponíveis para broadcasting.
    |
    */

    'connections' => [

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
