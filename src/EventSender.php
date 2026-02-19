<?php

namespace Daicar\EventSender\Laravel;

use Daicar\EventSender\Laravel\Contracts\QueueDriverInterface;
use Daicar\EventSender\Laravel\Support\BackupStore;
use Daicar\EventSender\Laravel\Support\Timestamp;

class EventSender
{
    protected $driver;
    protected $backup;

    public function __construct(
        QueueDriverInterface $driver,
        BackupStore $backup
    ) {
        $this->driver = $driver;
        $this->backup = $backup;
    }

    public function send(string $product, array $data)
    {
        $timestamp = Timestamp::nowUtcMillis();

        $payload = array_merge([
            'timestamp' => $timestamp
        ], $data);

        $attributes = $this->buildAttributesFromPayload($product, $timestamp);

        $file = $this->backup->store($payload);

        try {
            $response = $this->driver->send($payload, $attributes);
        } catch (\Throwable $e) {
            throw $e;
        }

        $this->backup->delete($file);

        return $response;
    }

    public function retry(): void
    {
        foreach ($this->backup->all() as $file) {
            $payload = $this->backup->read($file);

            if (empty($payload)) {
                continue;
            }

            if (empty($payload['timestamp']) || empty($payload['product'])) {
                throw new \InvalidArgumentException(
                    'Invalid payload format in backup file: ' . $file
                );
            }

            $attributes = $this->buildAttributesFromPayload($payload['product'], $payload['timestamp']);

            $this->driver->send($payload, $attributes);
            $this->backup->delete($file);
        }
    }

    public function pending(): array
    {
        return $this->backup->all();
    }

    protected function buildAttributesFromPayload(string $product, string $timestamp): array
    {
        return [
            'product' => [
                'DataType' => 'String',
                'StringValue' => $product
            ],
            'eventDatetime' => [
                'DataType' => 'String',
                'StringValue' => $timestamp
            ]
        ];
    }
}
