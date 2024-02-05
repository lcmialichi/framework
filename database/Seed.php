<?php

namespace Database;

use QueryBuilder\QueryBuilder;
use App\ORM\Connection\ConnectionHandler;

abstract class Seed
{
    public function __construct(private ConnectionHandler $connectionHandler)
    {
    }

    protected function queryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->connectionHandler->getConnection());
    }

    protected function create(string $table, array $data): void
    {
        $params = [];
        $values = [];
        foreach ($data as $key => $value) {
            $params[":{$key}"] = $value;
            $values[$key] = ":{$key}";
        }

        $this->queryBuilder()
            ->insert($values)
            ->into($table)
            ->addParams($params)
            ->execute();
    }

}


