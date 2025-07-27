<?php

// config reverb for Htop
return [
    'BROADCAST_DRIVER' => env('HTOP_DRIVER', 'sqlite'),
    'connection' => env('HTOP_DB_CONNECTION', 'htop_sqlite'),
];
