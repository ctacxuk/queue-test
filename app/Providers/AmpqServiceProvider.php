<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

class AmpqServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            AbstractConnection::class,
            function () {
                $config = [
                    config('amqp.host'),
                    config('amqp.port'),
                    config('amqp.user'),
                    config('amqp.pass'),
                    config('amqp.vhost'),
                ];
                $connection = new AMQPStreamConnection(...$config);
                return $connection;
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /** @var AbstractConnection $connection */
        $connection = app()->make(AbstractConnection::class);
        $connection->channel();
    }
}
