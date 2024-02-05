<?php

namespace Database;

use QueryBuilder\Macro\Create;
use QueryBuilder\QueryBuilder;
use App\ORM\Connection\ConnectionHandler;

abstract class Migration
{
    public function __construct(private ConnectionHandler $connectionHandler)
    {
    }

    public function raw(string $sql): void
    {
        $this->queryBuilder()->raw($sql)->execute();
    }

    public function queryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->connectionHandler->getConnection());
    }

    public function create(): Create
    {
        return $this->queryBuilder()->create();
    }

    public abstract function bindSeeds(): array;
}


