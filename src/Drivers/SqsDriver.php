<?php

namespace Daicar\EventSender\Laravel\Drivers;

use Aws\Sqs\SqsClient;
use Daicar\EventSender\Laravel\Contracts\QueueDriverInterface;
use Daicar\EventSender\Laravel\Exceptions\DriverException;

class SqsDriver implements QueueDriverInterface
{
    /** @var SqsClient */
    protected $client;

    /** @var string */
    protected $queueUrl;

    public function __construct(SqsClient $client, string $queueUrl)
    {
        if (empty($queueUrl)) {
            throw new \InvalidArgumentException(
                'SQS queue_url not configured.'
            );
        }

        $this->client = $client;
        $this->queueUrl = $queueUrl;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $attributes
     * @return string
     */
    public function send(array $payload, array $attributes): string
    {
        try {
            $response = $this->client->sendMessage([
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => json_encode($payload),
                'MessageAttributes' => $attributes
            ]);
        } catch (\Exception $e) {
            throw new DriverException('SQS', $payload, $attributes, $e);
        }

        return $response->get('MessageId');
    }
}
