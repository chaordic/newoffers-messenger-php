<?php

namespace Linx\Messenger\Contracts;

interface MessengerClient
{
    public function createTopic(string $name): bool;

    public function deleteTopic(string $name): bool;

    public function subscribe(string $topic, string $endpoint): bool;

    public function confirmSubscription(string $topic, string $token): bool;

    public function unsubscribe(string $topic, string $subscriptionId): bool;
    
    public function publish(string $topic, array $message, array $messageAttributes): bool;
    
    /**
     * $messages = [
     *   0 => [
     *    'topic' => '',
     *    'message' => [],
     *    'messageAttributes' => []
     *  ]
     * ]
     *
     * Publish Async receive a array of messages to be sent as async
     *
     * @param array $messages
     * @return array
     **/
    public function publishAsync(array $messages): array;
}
