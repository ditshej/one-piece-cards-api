<?php

return [
    'vegapull_path' => storage_path('vegapull'),
    'vegapull_binary' => env('VEGAPULL_BINARY', 'vega'),
    'schedule_enabled' => env('IMPORT_SCHEDULE_ENABLED', false),

    'sync_host' => env('SYNC_HOST'),
    'sync_user' => env('SYNC_USER'),
    'sync_port' => env('SYNC_PORT', 22),
    'sync_path' => env('SYNC_PATH'),
];
