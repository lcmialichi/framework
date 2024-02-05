<?php

namespace App\ORM;

class ModelCollection
{
    public function __construct(private array $models)
    {
    }

    public function map(callable $callable): self
    {
        return new self(array_map($callable, $this->models));
    }

    public function first(): ?Model
    {
        return $this->models[0] ?? null;
    }

    public function empty(): bool
    {
        return empty($this->models);
    }

}
