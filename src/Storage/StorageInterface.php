<?php

namespace Htop\Storage;

interface StorageInterface
{
    public function store(array $data): void;

    public function all(): array;
}
