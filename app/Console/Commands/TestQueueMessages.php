<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class TestQueueMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:test-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test messages';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        /** @var AbstractConnection $connection */
        $connection = app()->make(AbstractConnection::class);

        $channel = $connection->channel();

        $exchange = config('queue.exchange_name');

        for ($i = 0; $i < 1000; $i++) {


            for ($j = 1; $j <= 10; $j++) {
                $accountId = rand(1, 1000);
                $payload = json_encode([
                    'account_id' => $accountId,
                    'event_id' => $j,
                ]);

                $message = new AMQPMessage($payload, []);
                $message->set('application_headers', new AMQPTable([
                    'x-account-id' => $accountId,
                ]));



                $channel->basic_publish(msg: $message, exchange: $exchange);
            }


        }
    }
}
