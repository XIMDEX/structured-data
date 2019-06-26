<?php

return [
    'routes' => [
        'load-entity' => 'entity',
        'load-value' => 'values',
        'load-entity-nodes' => 'entity-nodes',
        'load-node' => 'node',
        'schema' => 'schema',
        'available-type' => 'available-types',
        'property-schema' => 'property-schema',
        'schemas-import' => 'schemas-import',
        'validate-entity' => 'validate'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
