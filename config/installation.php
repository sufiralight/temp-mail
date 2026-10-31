<?php

return [
    'enabled' => (bool) env('INSTALLATION_ENABLED', false),
    'directory' => env('INSTALLATION_DIRECTORY', storage_path('app/private/installation')),
    'database_hosts' => array_filter(explode(',', env('INSTALLATION_DATABASE_HOSTS', '127.0.0.1,localhost'))),
    'database_port' => (int) env('INSTALLATION_DATABASE_PORT', 3306),
    'tls_required' => (bool) env('INSTALLATION_TLS_REQUIRED', true),
    'tls_ca' => env('INSTALLATION_TLS_CA'),
];
