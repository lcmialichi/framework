<?php

namespace App\Event;

use App\Event\EventHandler;
use PhpAmqpLib\Message\AMQPMessage;
use App\Event\Connection\ConnectionHandler;

class Publisher
{
    public function __construct(private EventHandler $event)
    {
    }

    public function publish(): void
    {
        $connection = $this->getConnectionHandler()->getConnection();
        $channel = $connection->channel();

        $message = new AMQPMessage(
            json_encode($this->event->payload()),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        foreach ($this->event->channels() as $channelName) {
            $channel->queue_declare($channelName, false, false, false, false);
            $channel->basic_publish($message, '', $channelName);
        }

        $channel->close();
        $connection->close();
    }

    public function getConnectionHandler(): ConnectionHandler
    {
        return resolve(ConnectionHandler::class);
    }


}
