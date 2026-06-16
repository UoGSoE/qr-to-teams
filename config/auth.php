<?php

use App\Models\User;

return [

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'ldapusers',
        ],
    ],

    'providers' => [
        'ldapusers' => [
            'driver' => 'ldapeloquent',
            'model' => User::class,
        ],
    ],

];
