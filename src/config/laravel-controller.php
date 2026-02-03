<?php

return [
    'keys' => [
        'success' => 'success',
        'code' => 'code',
        'message' => 'message',
        'data' => 'data',
        'errors' => 'errors',
        'meta' => 'meta',
        'trace_id' => 'trace_id',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | These options control the default routes registered by the package.
    | You can disable them entirely or change the URL prefix.
    |
    */
    'routes' => [
        'enabled' => true,
        'prefix' => 'api/v1',
    ],
];
