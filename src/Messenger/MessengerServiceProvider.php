<?php

namespace Linx\Messenger;

use Illuminate\Support\ServiceProvider;
use Linx\Messenger\Contracts\MessengerClient;
use Aws\Sns\SnsClient;

class MessengerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->bindSns();

        $this->app->bind(MessengerClient::class, SnsClient::class);
    }

    private function bindSns()
    {
        $this->app->bind(SnsClient::class, function () {
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

            return new SnsClient($credentials);
        });
    }
}
