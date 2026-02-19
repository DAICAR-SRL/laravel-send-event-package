<?php

use Aws\Sqs\SqsClient;
use Daicar\EventSender\Laravel\Drivers\SqsDriver;
use Daicar\EventSender\Laravel\EventSender;
use Daicar\EventSender\Laravel\Support\BackupStore;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

error_reporting(E_ALL ^ E_USER_DEPRECATED);
class SendTest extends TestCase
{
    public function testSend(): void
    {
        $backupStore = $this->createMock(BackupStore::class);

        // fake client -> success
        $sender = new EventSender(
            new SqsDriver($this->createMockSqsClient(), 'asd'),
            $backupStore
        );

        $backupStore->expects($this->once())
            ->method('delete');

        $response = $sender->send('test', ['foo' => 'bar']);

        $this->assertEquals('123', $response);
    }

    public function testSendFail(): void
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

        $sender->send('test', ['foo' => 'bar']);


        // real driver and real client -> throw exception
        $this->expectException(\UnexpectedValueException::class);

        $backupStore = $this->createMock(BackupStore::class);

        $backupStore->expects($this->never())
            ->method('delete');

        $sender = new EventSender(
            new SqsDriver(new SqsClient([]), 'asd'),
            $backupStore
        );

        $response = $sender->send('test', ['foo' => 'bar']);
    }

    public function testSendHistorical(): void
    {
        // fake client -> success
        $sender = new EventSender(
            new SqsDriver($this->createMockSqsClient(), 'asd'),
            $this->createMockBackupStore()
        );

        $dateTime = new Carbon('2024-12-31 23:58:01');

        $response = $sender->send('test', ['foo' => 'bar'], $dateTime);

        $this->assertEquals('123', $response);
    }

    public function testSendHistoricalFail(): void
    {
        $sender = new EventSender(
            new SqsDriver(new SqsClient([]), 'asd'),
            $this->createMockBackupStore()
        );

        $this->expectException(\TypeError::class);

        $dateTime = '2024-12-31 23:58:01';

        /**
         * @disregard P1006
         */
        $sender->send('test', ['foo' => 'bar'], $dateTime);
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
