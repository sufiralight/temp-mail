<?php

return [
    'local' => env('INSTALL_LOCAL', false),
    'operator' => env('INSTALL_OPERATOR'),
    'path' => storage_path('app/private/installation'),
];
