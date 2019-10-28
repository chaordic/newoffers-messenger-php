<?php

namespace Linx\Messenger;

use Linx\Messenger\Contracts\MessengerClient;

class Messenger
{
    private $client;

    public function __construct(MessengerClient $client)
    {
        $this->setClient($client);
    }

    public function getClient(): MessengerClient
    {
        return $this->client;
    }

    public function setClient(MessengerClient $client)
    {
        $this->client = $client;

        return $this;
    }

    public function createTopic(string $name)
    {
        return $this->getClient()->createTopic($name);
    }

    public function deleteTopic(string $name)
    {
        return $this->getClient()->deleteTopic($name);
    }

    public function subscribe(string $topic, string $endpoint)
    {
        return $this->getClient()->subscribe($topic, $endpoint);
    }

    public function confirmSubscription(string $topic, string $token)
    {
        return $this->getClient()->confirmSubscription($topic, $token);
    }

    public function unsubscribe(string $topic, string $subscription)
    {
        return $this->getClient()->unsubscribe($topic, $subscription);
    }

    public function publish(string $topic, array $message, $messageAttributes = [])
    {
        return $this->getClient()->publish($topic, $message, $messageAttributes);
    }
    
    public function publishAsync(string $topic, array $message, $messageAttributes = [])
    {
        return $this->getClient()->publishAsync($topic, $message, $messageAttributes);
    }
}
