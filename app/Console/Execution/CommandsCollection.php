<?php

namespace App\Console\Execution;

class CommandsCollection
{
    /**
     * @param array<string><Command> $commands
     */
    public function __construct(private array $commands)
    {
    }

    public function findByName(string $name): ?Command
    {
        $section = "general";
        if(str_contains($name, ":")) {
            $section = explode(":", $name)[0];
        }
        return $this->commands[$section][$name] ?? null;
    }

    public function asArray(): array
    {
        return $this->commands;
    }
}

