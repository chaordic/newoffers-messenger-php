<?php

use Linx\Messenger\Clients\Sns;
use Aws\Sns\SnsClient as AwsSns;

class SnsClientTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->awsSnsMock = Mockery::mock(AwsSns::class);

        $this->snsClient = new Sns($this->awsSnsMock);
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testCreateTopic()
    {
        $this->awsSnsMock
            ->shouldReceive('listTopics->toArray')
            ->once()
            ->andReturn(['Topics' => []]);

        $this->awsSnsMock->shouldReceive('createTopic')
            ->with(['Name' => 'topic-test'])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->createTopic('topic-test'));
    }

    public function testDeleteTopic()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:topic-to-delete'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock->shouldReceive('deleteTopic')
            ->with(['TopicArn' => 'arn:aws:sns:us-east-1:owner:topic-to-delete'])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->deleteTopic('topic-to-delete'));
    }

    public function testPublish()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock->shouldReceive('publish')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:topic',
                'MessageStructure' => 'json',
                'Message' => '{"default":"[\"message\"]"}',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->publish('topic', ['message']));
    }

    public function testPublishWithMessageAttributes()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock->shouldReceive('publish')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:topic',
                'MessageStructure' => 'json',
                'Message' => '{"default":"[\"message\"]"}',
                'MessageAttributes' => [
                    'atttribute1' => [
                        'DataType' => 'String',
                        'StringValue' => 'atrribute_value'
                    ],
                ],
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->publish('topic', ['message']));
    }

    public function testSubscribeHttp()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock
            ->shouldReceive('subscribe')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic',
                'Protocol' => 'http',
                'Endpoint' => 'http://localhost/',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'http://localhost/'));
    }

    public function testSubscribeHttps()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock
            ->shouldReceive('subscribe')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic',
                'Protocol' => 'https',
                'Endpoint' => 'https://localhost/',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'https://localhost/'));
    }

    public function testSubscribeEmail()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock
            ->shouldReceive('subscribe')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic',
                'Protocol' => 'email',
                'Endpoint' => 'subscriber@email.com',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'subscriber@email.com'));
    }

    public function testSubscribeSqs()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock
            ->shouldReceive('subscribe')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic',
                'Protocol' => 'sqs',
                'Endpoint' => 'arn:aws:sqs:us-east-1:064250947333:sqs-table',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'arn:aws:sqs:us-east-1:064250947333:sqs-table'));
    }

    public function testSubscribeLambda()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock
            ->shouldReceive('subscribe')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:created-topic',
                'Protocol' => 'lambda',
                'Endpoint' => 'arn:aws:lambda:us-east-1:064250947333:function:lambda-function',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'arn:aws:lambda:us-east-1:064250947333:function:lambda-function'));
    }

    public function testConfirmSubscription()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:confirm-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock->shouldReceive('confirmSubscription')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:confirm-topic',
                'Token' => 'token',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->confirmSubscription('confirm-topic', 'token'));
    }

    public function testUnsubscribe()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:unsubscribe-topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');

        $this->awsSnsMock->shouldReceive('unsubscribe')
            ->with([
                'SubscriptionArn' => 'arn:aws:sns:us-east-1:owner:unsubscribe-topic:subscription-id',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->unsubscribe('unsubscribe-topic', 'subscription-id'));
    }
}
