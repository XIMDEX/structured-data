<?php

return [
    'routes' => [
        'load-entity' => 'entity',
        'load-entity-nodes' => 'entity-nodes',
        'load-node' => 'node',
        'schema' => 'schema',
        'available-types' => 'property-available-types',
        'property-schema' => 'property-schema'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
