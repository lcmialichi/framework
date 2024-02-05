<?php

namespace App\ORM\Connection;

use QueryBuilder\Connection\Connection;
use App\ORM\Connection\Group\ConnectionGroup;

class ConnectionHandler
{
    public function __construct(private ConnectionGroup $connectionGroup)
    {
    }

    public function getConnection(): Connection
    {
        return new Connection(
            $this->connectionGroup->host(),
            $this->connectionGroup->user(),
            $this->connectionGroup->password(),
            $this->connectionGroup->database(),
            $this->connectionGroup->driver()
        );
    }

    public function getConnectionNoDatabase(): Connection
    {
        return new Connection(
            $this->connectionGroup->host(),
            $this->connectionGroup->user(),
            $this->connectionGroup->password(),
            null,
            $this->connectionGroup->driver()
        );
    }

    public static function make(ConnectionGroup $connectionGroup): self
    {
        return new self($connectionGroup);
    }
}
