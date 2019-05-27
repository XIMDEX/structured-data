<?php

return [
    'routes' => [
        'load-entity' => 'load-entity',
        'load-entity-nodes' => 'load-entity-nodes',
        'load-node' => 'load-node',
        'load-schema' => 'schema',
        'schemas' => 'schemas'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
