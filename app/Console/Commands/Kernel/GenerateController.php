<?php

namespace App\Console\Commands\Kernel;

use App\Console\Files\Generator;

class GenerateController extends Generator
{
    private string $command = "gen:controller";
    private string $description = "Generate a new controller";

    protected function name(): string
    {
        if ($name = $this->argument(2)) {
            return $name;
        }

        throw new \Exception("<class-name> is required for this command");
    }

    protected function stub(): string
    {
        return resource('stubs/controller.stub');
    }

    protected function namespace (): string
    {
        return "App\\Controller";
    }

    protected function folder(): string
    {
        return ROOT_PATH . "/app/Controller";
    }

}
