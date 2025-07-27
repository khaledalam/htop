<?php

namespace Htop\Storage;

class StorageManager
{
    public function store(array $data): void
    {
        $this->driver()->store($data);
    }

    public function all(): array
    {
        return $this->driver()->all();
    }

    protected function driver(): StorageInterface
    {
        $driver = config('htop.driver', 'json');

        return match ($driver) {
            'json' => new JsonStorage(),
            default => new SQLiteStorage(),
        };
    }

}
