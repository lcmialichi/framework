<?php

namespace App\ORM;

use App\ORM\Row;
use App\ORM\Reflection;
use QueryBuilder\Macro\Delete;
use QueryBuilder\Macro\Insert;
use QueryBuilder\Macro\Select;
use QueryBuilder\Macro\Update;
use QueryBuilder\QueryBuilder;
use App\ORM\Attributes\Table;
use QueryBuilder\Connection\Connection;
use QueryBuilder\Connection\QueryResult;
use App\ORM\Connection\ConnectionHandler;
use App\ORM\Connection\Group\ConnectionGroup;

abstract class Orchestrator
{
    protected array $generatedValues = [];

    protected string $primaryKey = "id";

    protected ?string $table = null;

    public final function __construct(private array $rows = [])
    {
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public abstract function connectionGroup(): ConnectionGroup;

    public function getConnectionHandler(): ConnectionHandler
    {
        return ConnectionHandler::make($this->connectionGroup());
    }

    protected function getConnection(): Connection
    {
        return $this->getConnectionHandler()->getConnection();
    }

    protected function queryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->getConnection());
    }

    public function getTableName(): string
    {
        $table = $this->getReflection()->getClassAttribute(
            Table::class,
            fn($instance) => $instance->getName()
        );

        $shortName = strtolower($this->getReflection()->getShortName());
        return $table[0] ?? $this->table ?? $shortName;
    }

    public function select(mixed $columns = null): Select
    {
        return $this->queryBuilder()->select($columns)->from($this->getTableName());
    }

    public function insert(array $values): Insert
    {
        return $this->queryBuilder()->insert($values)->into($this->getTableName());
    }

    public function update(array $data): Update
    {
        return $this->queryBuilder()->update($this->getTableName())->set($data);
    }

    public function delete(): Delete
    {
        return $this->queryBuilder()->delete()->from($this->getTableName());
    }

    public static function create(array $data): QueryResult
    {
        return self::makeInstance()->insert($data)->execute();
    }

    public static function find(int $id): ?self
    {
        return self::findWhere(self::makeInstance()->getPrimaryKey(), "=", $id)->first();
    }

    public static function findWhere(string $column, string $operator, mixed $value): ModelCollection
    {
        $model = self::makeInstance();
        $result = $model->select()->where($column, $operator, $value)->execute();
        return $model->fetchClass($result);
    }

    public static function all(): ModelCollection
    {
        $model = self::makeInstance();
        return $model->fetchClass($model->select()->execute());
    }

    private function getReflection(): Reflection
    {
        return new Reflection($this);
    }

    private function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public static function makeInstance(): self
    {
        return resolve(get_called_class());
    }

    /** @return array<self> */
    private function fetchClass(QueryResult $queryResult): ModelCollection
    {
        return new ModelCollection(
            $queryResult->fetchFunction(function ($row) {
                return resolve(get_called_class(), ["rows" => $row]);
            })
        );
    }
}
