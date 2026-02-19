<?php

namespace Daicar\EventSender\Laravel\Drivers;

use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;
use Daicar\EventSender\Laravel\Contracts\QueueDriverInterface;

class SqsDriver implements QueueDriverInterface
{
    protected $client;
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

    public function send(array $payload, array $attributes): string
    {
        try {
            $response = $this->client->sendMessage([
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => json_encode($payload),
                'MessageAttributes' => $attributes
            ]);
        } catch (SqsException $e) {
            throw new \UnexpectedValueException(
                'Response error sending SQS message: ' . $e->getMessage(),
                $e->getCode(),
            );
        } catch (\Throwable $e) {
            throw new \Exception(
                'Error sending SQS message: ' . $e->getMessage(),
                $e->getCode(),
            );
        }

        return $response->get('MessageId');
    }
}
