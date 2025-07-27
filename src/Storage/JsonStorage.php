<?php

namespace Htop\Storage;

class JsonStorage implements StorageInterface
{
    protected string $file;

    public function __construct()
    {
        $this->file = storage_path('logs/htop.json');
        if (!file_exists($this->file)) file_put_contents($this->file, '[]');
    }

    public function store(array $data): void
    {
        $entries = json_decode(file_get_contents($this->file), true) ?? [];
        $entries[] = $data;
        file_put_contents($this->file, json_encode($entries, JSON_PRETTY_PRINT));
    }

    public function all(): array
    {
        return json_decode(file_get_contents($this->file), true) ?? [];
    }
}
