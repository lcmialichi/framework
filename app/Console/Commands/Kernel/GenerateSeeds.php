<?php

namespace App\Console\Commands\Kernel;

use App\Console\Files\Generator;

class GenerateSeeds extends Generator
{
    public $command = "gen:seeds";
    public $description = "generate a seed class";
    public $options = [
        "<class-name>" => "name of seed class"
    ];

    protected function namespace (): string
    {
        return config("database.seed.namespace");
    }

    protected function stub(): string
    {
        return resource('stubs/seeds.stub');
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
        return config("database.seed.path");
    }
}
