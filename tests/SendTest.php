<?php

use Aws\Sqs\SqsClient;
use Daicar\EventSender\Laravel\Drivers\SqsDriver;
use Daicar\EventSender\Laravel\EventSender;
use Daicar\EventSender\Laravel\Support\BackupStore;
use PHPUnit\Framework\TestCase;

error_reporting(E_ALL ^ E_USER_DEPRECATED);
class SendTest extends TestCase
{
    public function testSend(): void
    {
        // fake driver
        $sender = new EventSender(
            $this->createMockDriver(),
            $this->createMockBackupStore()
        );

        $response = $sender->send('test', ['foo' => 'bar']);

        $this->assertNotEquals('123', $response);


        // real driver and real client -> throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SQS queue_url not configured.');

        $sender = new EventSender(
            new SqsDriver(new SqsClient([]), ''),
            $this->createMockBackupStore()
        );

        $response = $sender->send('test', ['foo' => 'bar']);


        // fake client -> success
        $sender = new EventSender(
            new SqsDriver($this->createMockSqsClient(), 'asd'),
            $this->createMockBackupStore()
        );

        $response = $sender->send('test', ['foo' => 'bar']);

        $this->assertEquals('123', $response);
    }

    private function createMockDriver(): SqsDriver
    {
        return $this->createMock(SqsDriver::class);
    }

    private function createMockSqsClient(): SqsClient
    {
        $mock = $this->getMockBuilder(SqsClient::class)
            ->disableOriginalConstructor()
            ->addMethods(['sendMessage'])
            ->getMock();

        $mock->expects($this->once())
            ->method('sendMessage')
            ->willReturn(new \Aws\Result(['MessageId' => '123']));

        return $mock;
    }

    private function createMockBackupStore(): BackupStore
    {
        return new BackupStore('/tmp');
    }
}
