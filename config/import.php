<?php

return [
    'vegapull_path' => storage_path('vegapull'),
    'vegapull_binary' => env('VEGAPULL_BINARY', 'vega'),
    'schedule_enabled' => env('IMPORT_SCHEDULE_ENABLED', false),
];
