<?php



namespace App\Console\Commands\Kernel;

use App\Console\Files\Generator;

class GenerateValidation extends Generator
{
    public $command = "gen:validation";
    public $description = "generate a validation class";
    public $options = [
        "<class-name>" => "name of validation class"
    ];

    protected function namespace (): string
    {
        return config("validation.handlers.namespace");
    }

    protected function stub(): string
    {
        return resource('stubs/validation.stub');
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
        return config("validation.handlers.path");
    }
}
