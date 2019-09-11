<?php

namespace Linx\Messenger;

use Illuminate\Support\ServiceProvider;
use Linx\Messenger\Contracts\MessengerClient;
use Aws\Sns\SnsClient as AwsSnsClient;
use Linx\Messenger\Clients\Sns;

class MessengerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->bindAwsSns();

        $this->bindSnsClient();

        $this->app->bind(MessengerClient::class, Sns::class);
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
        $this->app->bind(Sns::class, function($app){
            return new Sns(
                $app->make(AwsSnsClient::class),
                config('aws.account_id')
            );
        });
    }
}
