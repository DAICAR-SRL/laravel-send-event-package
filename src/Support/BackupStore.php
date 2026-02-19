<?php

namespace Daicar\EventSender\Laravel\Support;

use Daicar\EventSender\Laravel\Exceptions\BackupException;

class BackupStore
{
    protected $path;

    public function __construct(string $path)
    {
        $this->path = rtrim($path, '/');
    }

    public function store(array $payload): string
    {
        try {
            if (!is_dir($this->path)) {
                mkdir($this->path, 0777, true);
            }

            $file = $this->path . '/' . uniqid('event_', true) . '.json';
        } catch (\Throwable $e) {
            throw new BackupException('', $payload, $e);
        }

        $result = file_put_contents($file, json_encode($payload));

        if ($result === false) {
            throw new BackupException($file, $payload);
        }

        return $file;
    }

    public function all(): array
    {
        if (!is_dir($this->path)) {
            return [];
        }

        return glob($this->path . '/*.json') ?: [];
    }

    public function read(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }

    public function delete(string $file): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
