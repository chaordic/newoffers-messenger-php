<?php

use Linx\Messenger\Clients\Sns;
use Aws\Sns\SnsClient as AwsSns;
use Aws\Sns\Exception\SnsException;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\FulfilledPromise;

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
                'MessageAttributes' => [],
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

        $messageAttibutes = [
            'atttribute1' => [
                'DataType' => 'String',
                'StringValue' => 'atrribute_value'
            ],
        ];

        $messageData = [
            'TopicArn' => 'arn:aws:sns:us-east-1:owner:topic',
            'MessageStructure' => 'json',
            'Message' => '{"default":"[\"message\"]"}',
            'MessageAttributes' => $messageAttibutes,
        ];

        $this->awsSnsMock->shouldReceive('publish')
            ->with($messageData)
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->publish('topic', ['message'], $messageAttibutes));
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
                'Endpoint' => 'arn:aws:sqs:us-east-1:064250947333:sqs-table-v2',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'arn:aws:sqs:us-east-1:064250947333:sqs-table-v2'));
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
                'Endpoint' => 'arn:aws:lambda:us-east-1:064250947333:function:lambda-function-v2',
            ])
            ->once()
            ->andReturn(true);

        $this->assertTrue($this->snsClient->subscribe('created-topic', 'arn:aws:lambda:us-east-1:064250947333:function:lambda-function-v2'));
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

    public function testPublishAsync()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');
        
        $mockCommand = Mockery::mock(Aws\Command::class);

        $promiseF = new FulfilledPromise($mockCommand);

        $mockCommand->shouldReceive('get')
            ->with('MessageId')
            ->andReturn('123');

        $this->awsSnsMock->shouldReceive('publishAsync')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:topic',
                'MessageStructure' => 'json',
                'Message' => '{"default":"[\"message\"]"}',
                'MessageAttributes' => []
            ])
            ->once()
            ->andReturn($promiseF);
        
        $promise = $this->snsClient->publishAsync(
            'topic',
            ['message']
        );
        $this->assertEquals('123', $promise->wait());
    }

    public function testPublishAsyncCreate()
    {
        $this->awsSnsMock->shouldReceive('listTopics->toArray')->andReturn(['Topics' => [
            ['TopicArn' => 'arn:aws:sns:us-east-1:owner:topic'],
        ]]);

        $this->awsSnsMock
            ->shouldReceive('getRegion')
            ->once()
            ->andReturn('us-east-1');
        $mockCommand = Mockery::mock(Aws\Command::class);
        $promiseRejected = new RejectedPromise(new SnsException("error", $mockCommand));
        $promiseFulfilled = new FulfilledPromise($mockCommand);

        $this->awsSnsMock->shouldReceive('publishAsync')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:topic-new',
                'MessageStructure' => 'json',
                'Message' => '{"default":"[\"message\"]"}',
                'MessageAttributes' => []
            ])
            ->once()
            ->andThrow($promiseRejected);

        $this->awsSnsMock->shouldReceive('createTopic')
            ->with(['Name' => 'topic-new'])
            ->once()
            ->andReturn(true);

        $this->awsSnsMock->shouldReceive('publishAsync')
            ->with([
                'TopicArn' => 'arn:aws:sns:us-east-1:owner:topic-new',
                'MessageStructure' => 'json',
                'Message' => '{"default":"[\"message\"]"}',
                'MessageAttributes' => []
            ])
            ->once()
            ->andReturn($promiseFulfilled);

        $mockCommand->shouldReceive('get')
            ->with('MessageId')
            ->andReturn('123');

        $promise = $this->snsClient->publishAsync(
            'topic-new',
            ['message']
        );
        $this->assertEquals('123', $promise->wait());
    }
}
