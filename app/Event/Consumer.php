<?php

namespace App\Event;

use App\Event\Connection\ConnectionHandler;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Consumer
{
    private AMQPStreamConnection $connection;
    private $channel;

    public function __construct(
        ConnectionHandler $connectionHandler
    ) {
        $this->connection = $connectionHandler->getConnection();
        $this->channel = $this->connection->channel();
    }

    public function add(Listener $listener): void
    {
        $this->channel->queue_declare($listener->name(), false, false, false, false);
        $this->channel->basic_consume(
            $listener->name(),
            '',
            false,
            true,
            false,
            false,
            function ($message) use ($listener) {
                $listener->handle(json_decode($message->body, true));
            }
        );
    }

    public function listen(): void
    {
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

}
