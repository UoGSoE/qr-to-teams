<?php

return [
    'server' => env('LDAP_SERVER'),
    'ou' => env('LDAP_OU'),
    'authentication' => env('LDAP_AUTHENTICATION', true),
    'username' => env('LDAP_USERNAME'),
    'password' => env('LDAP_PASSWORD'),
];
