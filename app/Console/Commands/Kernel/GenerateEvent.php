<?php

namespace App\Console\Commands\Kernel;

use App\Console\Files\Generator;

class GenerateEvent extends Generator
{
    public $command = "gen:event";
    public $description = "generate a event class";
    public $options = [
        "<class-name>" => "name of command class"
    ];

    protected function namespace (): string
    {
        return config("events.events.namespace");
    }

    protected function stub(): string
    {
        return resource('stubs/event.stub');
    }

    protected function name(): string
    {
        if ($name = $this->argument(2)) {
            return $name;
        }
        throw new \Exception("<class-name> is required for this command");

    }

    protected function folder(): string
    {
        return __DIR__ . "/../../../Event/Events";
    }
}
