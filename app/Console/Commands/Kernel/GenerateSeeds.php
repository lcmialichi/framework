<?php

namespace App\Console\Commands\Kernel;

use App\FileSystem\Stream;
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
        return $this->console()->argument(2);
    }

    protected function folder(): string
    {
        return config("database.seed.path");
    }
}
