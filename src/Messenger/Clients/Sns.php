<?php

namespace Linx\Messenger\Clients;

use Exception;
use Aws\Sns\SnsClient;
use Aws\Sns\Exception\SnsException;
use Linx\Messenger\Contracts\MessengerClient;
use Linx\Messenger\Exceptions\NoResourceFoundException;

class Sns implements MessengerClient
{
    private $snsClient;

    private $accountId;

    private $region;

    private $arnTopics = [];

    private $topics = [];

    public function __construct(SnsClient $snsClient, string $accountId = null)
    {
        $this->snsClient = $snsClient;
        $this->accountId = $accountId;
    }

    private function refreshTopics($token = null)
    {
        $topics = $this->snsClient->listTopics(['NextToken' => $token])->toArray();
        $nextToken = $topics['NextToken'] ?? false;

        array_walk($topics['Topics'], function ($topic) {
            array_push($this->arnTopics, $topic['TopicArn']);
            array_push($this->topics, $this->getNameFromArn($topic['TopicArn']));
        });

        if($nextToken !== false) {
            $this->refreshTopics($nextToken);
        }
    }

    private function getTopics()
    {
        if (empty($this->topics)) {
            $this->refreshTopics();
        }

        return $this->topics;
    }

    private function getArnTopics()
    {
        if (empty($this->arnTopics)) {
            $this->refreshTopics();
        }

        return $this->arnTopics;
    }

    private function emptyTopics()
    {
        $this->topics = [];
        $this->arnTopics = [];
    }

    private function getArnFromName($name)
    {
        if(!$this->region) {
            $this->region = $this->snsClient->getRegion();
        }

        if(!$this->accountId) {
            $topics = $this->getArnTopics();
            if (empty($topics)) {
                throw new NoResourceFoundException();
            }
            $this->accountId = (explode(':', $topics[0]))[4];
        }

        return "arn:aws:sns:{$this->region}:{$this->accountId}:{$name}";
    }

    private function getNameFromArn($arn)
    {
        $parts = explode(':', $arn);
        $name = end($parts);

        return $name;
    }

    private function getDataToPublish(string $topic, array $message, $messageAttributes = []) : array
    {
        //Up to 256KB of Unicode text.
        $messageEncoded = json_encode($message);

        return [
            'TopicArn' => $this->getArnFromName($topic),
            'MessageStructure' => 'json',
            'Message' => json_encode(['default' => $messageEncoded]),
            'MessageAttributes' => $messageAttributes,
        ];
    }

    public function createTopic(string $name): bool
    {
        if (in_array($name, $this->getTopics())) {
            throw new Exception('This topic already exists.');
        }

        $this->snsClient->createTopic([
            'Name' => $name,
        ]);

        $this->emptyTopics();

        return true;
    }

    public function deleteTopic(string $name): bool
    {
        if (!in_array($name, $this->getTopics())) {
            throw new Exception('This topic is not exists.');
        }

        $this->snsClient->deleteTopic([
            'TopicArn' => $this->getArnFromName($name),
        ]);

        $this->emptyTopics();

        return true;
    }

    public function subscribe(string $topic, string $endpoint): bool
    {
        $match = function ($endpoint) {
            if (preg_match('/^arn:aws:lambda:[a-z]{2}-[a-z]{3,12}-[1,2,3]:\d+:function:[a-zA-Z0-9-_]{1,256}$/', $endpoint)) {
                return 'lambda';
            } elseif (preg_match('/^arn:aws:sqs:[a-z]{2}-[a-z]{3,12}-[1,2,3]:\d+:[a-zA-Z0-9-_]{1,256}$/', $endpoint)) {
                return 'sqs';
            }

            return false;
        };

        if (filter_var($endpoint, FILTER_VALIDATE_URL)) {
            $protocol = parse_url($endpoint)['scheme'];
        } elseif (filter_var($endpoint, FILTER_VALIDATE_EMAIL)) {
            $protocol = 'email';
        } elseif ($protocol = $match($endpoint)); else {
            throw new Exception('Invalid endpoint.');
        }

        if (!in_array($topic, $this->getTopics())) {
            throw new Exception('This topic is not exists.');
        }

        $subscribe = $this->snsClient->subscribe([
            'TopicArn' => $this->getArnFromName($topic),
            'Protocol' => $protocol,
            'Endpoint' => $endpoint,
        ]);

        return true;
    }

    public function confirmSubscription(string $topic, string $token): bool
    {
        $this->snsClient->confirmSubscription([
            'TopicArn' => $this->getArnFromName($topic),
            'Token' => $token,
        ]);

        return true;
    }

    public function unsubscribe(string $topic, string $subscriptionId): bool
    {
        $this->snsClient->unsubscribe([
            'SubscriptionArn' => $this->getArnFromName($topic).':'.$subscriptionId,
        ]);

        return true;
    }

    public function publish(string $topic, array $message, $messageAttributes = []): bool
    {
        try {
            $data = $this->getDataToPublish($topic, $message, $messageAttributes);
            $this->snsClient->publish($data);
        } catch (SnsException $e) {
            if ('NotFound' !== $e->getAwsErrorCode()) {
                throw $e;
            }

            $this->createTopic($topic);

            $data = $this->getDataToPublish($topic, $message);
            $this->snsClient->publish($data);
        } catch (NoResourceFoundException $e) {
            $this->createTopic($topic);

            $data = $this->getDataToPublish($topic, $message);
            $this->snsClient->publish($data);
        }

        return true;
    }

    public function publishAsync(string $topic, array $message, $messageAttributes = [])
    {
        $dataToPublish = $this->getDataToPublish(
            $topic,
            $message,
            $messageAttributes
        );

        return $this->snsClient->publishAsync($dataToPublish)
            ->then(function ($result) {
                return $result->get('MessageId');
            }, function (SnsException $e) use ($topic, $message, $messageAttributes) {
                $this->createTopic($topic);
                return $this->publishAsync($topic, $message, $messageAttributes);
            }, function (NoResourceFoundException $ex) use ($topic, $message, $messageAttributes) {
                $this->createTopic($topic);
                return $this->publishAsync($topic, $message, $messageAttributes);
            });
    }
}
