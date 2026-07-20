<?php

$vegapullRelativePath = 'storage/vegapull';

return [
    'vegapull_relative_path' => $vegapullRelativePath,
    'vegapull_path' => base_path($vegapullRelativePath),
    'vegapull_binary' => env('VEGAPULL_BINARY', 'vega'),
    'vegapull_packs_file' => 'packs.json',
    'vegapull_cards_glob' => 'cards_*.json',
    'schedule_enabled' => env('IMPORT_SCHEDULE_ENABLED', false),

    'sync_host' => env('SYNC_HOST'),
    'sync_user' => env('SYNC_USER'),
    'sync_port' => env('SYNC_PORT', 22),
    'sync_path' => env('SYNC_PATH'),
    'sync_php' => env('SYNC_PHP') ?: 'php',
];
