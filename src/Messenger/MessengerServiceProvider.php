<?php

namespace Linx\Messenger;

use Aws\Sns\SnsClient as AwsSnsClient;
use Illuminate\Support\ServiceProvider;
use Linx\Messenger\Clients\ServiceBus;
use Linx\Messenger\Clients\Sns;
use Linx\Messenger\Contracts\MessengerClient;
use WindowsAzure\Common\ServicesBuilder as AzureServiceBusClient;

class MessengerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $messengerClient = env('MESSENGER', 'sns');

        switch ($messengerClient) {
            case 'sns':
                $this->bindAwsSns();
                $this->bindSnsClient();
                $this->app->bind(MessengerClient::class, Sns::class);
                break;
            case 'serviceBus':
                $this->bindAzureServiceBus();
                $this->bindServiceBusClient();
                $this->app->bind(MessengerClient::class, ServiceBus::class);
                break;
            default:
                break;
        }
    }

    private function bindAwsSns()
    {
        $this->app->bind(AwsSnsClient::class, function () {
            $credentials = [];
            if (config('aws.key')) {
                $credentials['credentials'] = [
                    'key' => config('aws.key'),
                    'secret' => config('aws.secret'),
                ];
            }

            if (config('aws.region')) {
                $credentials['region'] = config('aws.region');
            }

            if (config('aws.version')) {
                $credentials['version'] = config('aws.version');
            }

            return new AwsSnsClient($credentials);
        });
    }

    private function bindSnsClient()
    {
        $this->app->bind(Sns::class, function ($app) {
            return new Sns(
                $app->make(AwsSnsClient::class),
                config('aws.account_id')
            );
        });
    }

    private function bindAzureServiceBus()
    {
        $this->app->bind(AzureServiceBusClient::class, function () {
            $namespace = config('azure.service_bus.namespace');
            $keyName = config('azure.service_bus.key_name');
            $keyValue = config('azure.service_bus.key_value');

            $connectionString = "Endpoint=https://{$namespace}.servicebus.windows.net/;SharedAccessKeyName={$keyName};SharedAccessKey={$keyValue}";

            return AzureServiceBusClient::getInstance()->createServiceBusService($connectionString);
        });
    }

    private function bindServiceBusClient()
    {
        $this->app->bind(ServiceBus::class, function ($app) {
            return new ServiceBus(
                $app->make(AzureServiceBusClient::class)
            );
        });
    }
}
