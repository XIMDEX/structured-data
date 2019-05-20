<?php

return [
    'routes' => [
        'load-entity' => 'load-entity',
        'load-entities' => 'load-entities',
        'ping' => 'ping'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
