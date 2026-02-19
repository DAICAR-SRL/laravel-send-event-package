<?php

namespace Daicar\EventSender\Laravel;

use Daicar\EventSender\Laravel\Contracts\QueueDriverInterface;
use Daicar\EventSender\Laravel\Exceptions\SendException;
use Daicar\EventSender\Laravel\Support\BackupStore;
use Daicar\EventSender\Laravel\Support\Timestamp;
use Illuminate\Support\Carbon;

class EventSender
{
    /** @var QueueDriverInterface */
    protected $driver;

    /** @var BackupStore */
    protected $backup;

    public function __construct(
        QueueDriverInterface $driver,
        BackupStore $backup
    ) {
        $this->driver = $driver;
        $this->backup = $backup;
    }

    /**
     * @param string $product
     * @param array<string, mixed> $data
     * @param Carbon|null $dateTime
     * @return string
     */
    public function send(string $product, array $data, ?Carbon $dateTime = null): string
    {
        $timestamp = $dateTime ? Timestamp::utcMillisFromCarbon($dateTime) : Timestamp::nowUtcMillis();

        $payload = array_merge([
            'timestamp' => $timestamp,
            'product' => $product
        ], $data);

        $attributes = $this->buildAttributesFromPayload($product, $timestamp);

        $file = $this->backup->store($payload);

        try {
            $response = $this->driver->send($payload, $attributes);
        } catch (\Throwable $e) {
            throw new SendException($file, $attributes, $e);
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
                    . ' - timestamp: ' . $payload['timestamp'] . ', product: ' . $payload['product']
                );
            }

            $attributes = $this->buildAttributesFromPayload($payload['product'], $payload['timestamp']);

            $this->driver->send($payload, $attributes);
            $this->backup->delete($file);
        }
    }

    /**
     * @return array<int, string>
     */
    public function pending(): array
    {
        return $this->backup->all();
    }

    /**
     * @param string $product
     * @param string $timestamp
     * @return array<string, mixed>
     */
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
