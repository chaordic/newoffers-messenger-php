<?php

use Linx\Messenger\Clients\ServiceBus;
use WindowsAzure\Common\Internal\ServiceRestProxy;

class ServiceBusClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serviceBusRestProxyMock = Mockery::mock(ServiceRestProxy::class);

        $this->serviceBusClient = new ServiceBus($this->serviceBusRestProxyMock);
    }

    protected function tearDown()
    {
        Mockery::close();
    }

    public function testCreateTopic()
    {
        $this->serviceBusRestProxyMock
            ->shouldReceive('createTopic')
            ->once();

        $this->assertTrue($this->serviceBusClient->createTopic('topic-name'));
    }

    public function testDeleteTopic()
    {
        $this->serviceBusRestProxyMock
            ->shouldReceive('deleteTopic')
            ->once();

        $this->assertTrue($this->serviceBusClient->deleteTopic('topic-name'));
    }

    public function testSubscribe()
    {
        $this->serviceBusRestProxyMock
            ->shouldReceive('createSubscription')
            ->once();

        $this->assertTrue($this->serviceBusClient->subscribe('topic-name', 'endpoint'));
    }

    public function testConfirmSubscription()
    {
        // TODO: Implement confirmSubscription() method.
    }

    public function testUnsubscribe()
    {
        $this->serviceBusRestProxyMock
            ->shouldReceive('deleteSubscription')
            ->once();

        $this->assertTrue($this->serviceBusClient->unsubscribe('topic-name', 'sub-id'));
    }

    public function testPublish()
    {
        $this->serviceBusRestProxyMock
            ->shouldReceive('sendTopicMessage')
            ->once();

        $this->assertTrue($this->serviceBusClient->publish('topic-name', ['message']));
    }

    public function testPublishAsync()
    {
        // TODO: Implement confirmSubscription() method.
    }
}
