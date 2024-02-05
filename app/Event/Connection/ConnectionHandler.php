<?php



namespace App\Event\Connection;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class ConnectionHandler
{
    public function __construct(private Connection $connection)
    {
    }

    public function getConnection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            $this->connection->host(),
            $this->connection->port(),
            $this->connection->user(),
            $this->connection->password()
        );
    }
}
