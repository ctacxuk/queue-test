<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AbstractConnection;

class ConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consumer {--queue=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $queueName = $this->option('queue');

        /** @var AbstractConnection $connection */
        $connection = app()->make(AbstractConnection::class);
        $channel = $connection->channel();

        $channel->basic_qos(0, 1, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";


        //Простейшая реализация без job
        $callback = function ($msg) use ($queueName) {

            echo ' Received message', $msg->body, PHP_EOL;

            sleep(1);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };


        $channel->basic_consume(
            $queueName,
            '',
            false,
            false,
            false,
            false,
            $callback
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();

        return Command::SUCCESS;
    }
}
