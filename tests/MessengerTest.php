<?php

use Linx\Messenger\Messenger;
use Linx\Messenger\Contracts\MessengerClient;

class MessengerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->clientMock = Mockery::mock(MessengerClient::class);

        $this->messenger = new Messenger($this->clientMock);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testCreateTopic()
    {
        $this->clientMock->shouldReceive('publish')
            ->with('topic', ['message'])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->messenger->publish('topic', ['message']));
    }

    public function testDeleteTopic()
    {
        $this->clientMock->shouldReceive('deleteTopic')
            ->with('topic-to-delete')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->messenger->deleteTopic('topic-to-delete'));
    }

    public function testSubscribe()
    {
        $this->clientMock->shouldReceive('subscribe')
            ->with('topic-to-subscribe', 'http://localhost')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->messenger->subscribe('topic-to-subscribe', 'http://localhost'));
    }

    public function testConfirmSubscription()
    {
        $this->clientMock->shouldReceive('confirmSubscription')
            ->with('topic-to-confirm', 'xxxxxx-xxxxx')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->messenger->confirmSubscription('topic-to-confirm', 'xxxxxx-xxxxx'));
    }

    public function testUnsubscribe()
    {
        $this->clientMock->shouldReceive('unsubscribe')
            ->with('topic', 'subscription')
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->messenger->unsubscribe('topic', 'subscription'));
    }

    public function testPublish()
    {
        $this->clientMock->shouldReceive('publish')
            ->with('topic', ['message-to-publish'])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->messenger->publish('topic', ['message-to-publish']));
    }

    public function testPublishWithMessageAttributes()
    {
        $this->clientMock->shouldReceive('publish')
            ->with('topic', ['message-to-publish'],[])
            ->once()
            ->andReturn(true);

        $messageAttibutes = ['MessageAttributes' => [
            'atttribute1' => [
                'DataType' => 'String',
                'StringValue' => 'atrribute_value'
            ],
        ]];
        $this->assertTrue($this->messenger->publish('topic', ['message-to-publish'], $messageAttibutes));
    }
}
