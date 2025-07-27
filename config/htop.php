<?php

// config for Htop
return [
    'driver' => env('HTOP_DRIVER', 'sqlite'),
    'connection' => env('HTOP_DB_CONNECTION', 'htop_sqlite'),
];
