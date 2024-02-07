<?php

namespace App\Console\Commands\Kernel;

use App\Console\Files\Generator;

class GenerateListener extends Generator
{
    public $command = "gen:listener";
    public $description = "generate a listener class";
    public $options = [
        "<class-name>" => "name of command class"
    ];

    protected function namespace (): string
    {
        return config("events.listeners.namespace");
    }

    protected function stub(): string
    {
        return resource('stubs/listener.stub');
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
        return __DIR__ . "/../../../Event/Listeners";
    }
}
