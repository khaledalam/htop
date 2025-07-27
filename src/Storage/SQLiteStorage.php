<?php

namespace Htop\Storage;

use Htop\Database\HtopTableCreator;
use Illuminate\Support\Facades\DB;

class SQLiteStorage implements StorageInterface
{
    public function __construct()
    {
        $path = config('database.connections.htop_sqlite.database');
        if (! file_exists($path)) {
            file_put_contents($path, '');
        }

        HtopTableCreator::ensureTableExists();
    }

    public function store(array $data): void
    {
        DB::connection(config('htop.connection'))
            ->table('htop_requests')
            ->insert([
                'method' => $data['method'],
                'path' => $data['path'],
                'status' => $data['status'],
                'duration' => $data['duration'],
                'timestamp' => $data['timestamp'],
            ]);
    }

    public function all(): array
    {
        return DB::connection(config('htop.connection'))
            ->table('htop_requests')
            ->orderByDesc('timestamp')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
