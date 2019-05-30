<?php

return [
    'routes' => [
        'load-entity' => 'entity',
        'load-entity-nodes' => 'entity-nodes',
        'load-node' => 'node',
        'load-schema' => 'schema',
        'available-types' => 'property-available-types'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
