<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Wire\AMQPTable;

class DeclareCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:declare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rabbitmq declare';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        /** @var AbstractConnection $connection */
        $connection = app()->make(AbstractConnection::class);

        $channel = $connection->channel();

        //допустим есть какой-то exchange созданный в стороннем сервисе, который собирает события
        $channel->exchange_declare(
            exchange: 'events',
            type: 'direct',
            durable: true,
        );


        // создаем свой с типом x-consistent-hash, чтобы все события пользователя всегда были в одной очереди и биндим к стороннему
        $exchange = config('queue.exchange_name');

        $channel->exchange_declare(
            exchange: $exchange,
            type: 'x-consistent-hash',
            durable: true,
            arguments: new AMQPTable(['hash-header' => 'x-account-id'])
        );

        $channel->exchange_bind($exchange, 'events');

        $queueCount = config('queue.queue_count');


        // создаем нужное кол-во очередей с аргументом x-single-active-consumer, чтобы максимум был один консюмер, чтобы гарантировать последовательность выполнения
        for ($i = 1; $i <= $queueCount; $i++) {
            $channel->queue_declare(
                queue: "$exchange.$i",
                durable: true,
                auto_delete: false,
                arguments: new AMQPTable(['x-single-active-consumer' => true])
            );
            $channel->queue_bind(
                queue: "$exchange.$i",
                exchange: $exchange,
                routing_key: $i
            );
        }
    }
}
