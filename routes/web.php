<?php

use Htop\Storage\StorageManager;
use Illuminate\Support\Facades\Route;

Route::get('/htop', function () {
    return view('htop::dashboard');
})->name('htop');

Route::get('/htop-data', function () {
    $data = app(StorageManager::class)->all();

    return collect(array_reverse($data))
        ->take(100)
        ->map(fn ($r) => [
            'method' => $r->method ?? '',
            'path' => $r->path ?? '',
            'status' => $r->status ?? '',
            'duration' => $r->duration ?? '',
            'timestamp' => $r->timestamp ?? '',
        ])
        ->values();
})->name('htop-data');
