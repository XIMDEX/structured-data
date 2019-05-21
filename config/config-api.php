<?php

return [
    'routes' => [
        'load-entity' => 'load-entity',
        'load-entity-nodes' => 'load-entity-nodes',
        'load-node' => 'load-node'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
