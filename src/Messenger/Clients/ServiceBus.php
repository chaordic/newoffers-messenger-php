<?php

namespace Linx\Messenger\Clients;

use Linx\Messenger\Contracts\MessengerClient;
use WindowsAzure\Common\Internal\ServiceRestProxy;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\ServiceBus\Models\BrokeredMessage;
use WindowsAzure\ServiceBus\Models\SubscriptionInfo;
use WindowsAzure\ServiceBus\Models\TopicInfo;

class ServiceBus implements MessengerClient
{
    private $serviceBusRestProxy;

    public function __construct(ServiceRestProxy $serviceBusRestProxy)
    {
        $this->serviceBusRestProxy = $serviceBusRestProxy;
    }

    public function createTopic(string $name): bool
    {
        $topicInfo = new TopicInfo($name);
        $this->serviceBusRestProxy->createTopic($topicInfo);

        return true;
    }

    public function deleteTopic(string $name): bool
    {
        $this->serviceBusRestProxy->deleteTopic($name);
        return true;
    }

    public function subscribe(string $topic, string $endpoint): bool
    {
        $subscriptionInfo = new SubscriptionInfo($endpoint);
        $this->serviceBusRestProxy->createSubscription($topic, $subscriptionInfo);
        return true;
    }

    public function confirmSubscription(string $topic, string $token): bool
    {
        // TODO: Implement confirmSubscription() method.
    }

    public function unsubscribe(string $topic, string $subscriptionId): bool
    {
        $this->serviceBusRestProxy->deleteSubscription($topic, $subscriptionId);

        return true;
    }

    public function publish(string $topic, array $message, array $messageAttributes = []): bool
    {
        try {
            $topicMessage = new BrokeredMessage();
            $topicMessage->setBody(json_encode($message));
            $this->serviceBusRestProxy->sendTopicMessage($topic, $topicMessage);

            return true;
        } catch (ServiceException $e) {
            if (404 == $e->getCode() && $this->createTopic($topic)) {
                $topicMessage = new BrokeredMessage();
                $topicMessage->setBody(json_encode($message));
                $this->serviceBusRestProxy->sendTopicMessage($topic, $topicMessage);
            }

            return false;
        }
    }

    public function publishAsync(string $topic, array $message, $messageAttributes = [])
    {
        // TODO: Implement publishAsync() method.
    }
}
