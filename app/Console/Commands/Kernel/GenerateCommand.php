<?php

namespace App\Console\Commands\Kernel;

use App\FileSystem\Stream;
use App\Console\Files\Generator;

class GenerateCommand extends Generator
{
    public $command = "gen:command";
    public $description = "generate a command class";
    public $options = [
        "<class-name>" => "name of command class"
    ];

    protected function namespace (): string
    {
        return "App\\Console\\Commands";
    }

    protected function stub(): string
    {
        return resource('stubs/command.stub');
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
        return __DIR__ . "/..";
    }
}
