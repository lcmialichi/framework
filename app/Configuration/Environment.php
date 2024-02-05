<?php

namespace App\Configuration;

class Environment
{
    private array $env = [];

    public function __construct()
    {
        $this->env = getenv();
    }

    public static function make(): self
    {
        return new self();
    }

    public function get(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->env;
        }
        return $this->env[$key] ?? null;
    }

    public function __debugInfo(): array
    {
        return [];
    }
}
