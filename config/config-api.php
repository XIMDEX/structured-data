<?php

return [
    'routes' => [
        'load-item' => 'item',
        'load-value' => 'values',
        'load-item-nodes' => 'item-nodes',
        'load-node' => 'node',
        'schema' => 'schema',
        'available-type' => 'available-types',
        'property-schema' => 'property-schema',
        'schemas-import' => 'schemas-import',
        'validate-item' => 'validate'
    ],
    'middleware' => [
        'base' => 'api',
        'auth' => 'api'    // 'auth:api'
    ]
];
