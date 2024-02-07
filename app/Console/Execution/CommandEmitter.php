<?php

namespace App\Console\Execution;

use App\Console\Command;
use App\Helpers\ClassLoader;
use App\Container\ContainerInterface;
use App\Contracts\ExceptionHandlerInterface;
use App\Console\Execution\CommandsCollection;
use App\Console\Execution\Command as CommandEntity;

class CommandEmitter extends Command
{
    private ?string $currentExecution = null;
    private const OUTPUT_SIZE = 35;
    private const STRLEN_SIZE = 35;
    private const OPTIONS_SIZE = 75;
    private const GENERAL_SECTION = "general";
    private CommandsCollection $commands;

    public function __construct(
        private ContainerInterface $app,
        private ExceptionHandlerInterface $exceptionHandler
    ) {
        $this->currentExecution = $this->getCommandFromInput();
        $this->commands = $this->loadCommands();
    }

    public function getCommandFromInput()
    {
        return $this->argument(1);
    }

    public function getCommandsNamespace(): string
    {
        return config("app.command.namespace");
    }

    public function loadCommands(): CommandsCollection
    {
        $collection = [];
        foreach ($this->getCommands() as $command) {
            $commandInstance = new CommandEntity($command);
            $name = $commandInstance->command();
            if ($name === null) {
                $this->warning("Warning: Command {$command} does not have a command property. Skipping...");
                continue;
            }

            $section = self::GENERAL_SECTION;
            if (str_contains($name, ":")) {
                $section = explode(":", $name)[0];
            }

            $collection[$section][$commandInstance->command()] = $commandInstance;
        }

        return new CommandsCollection($collection);
    }

    private function getCommands(): array
    {
        $loader = new ClassLoader($this->getCommandsNamespace(), true);
        return $loader->load();
    }

    public function emitt(?string $command = null): void
    {
        $command = $command ?? $this->currentExecution;
        if ($command === null) {
            $this->show();
            die;
        }

        $this->dispatch($command);
    }

    private function show(): void
    {
        foreach ($this->commands->asArray() as $section => $commands) {
            $this->success(sprintf("%s:", $section));
            $this->showCommandsList($commands);
        }
    }

    /** @param array<CommandEntity> $commands */
    private function showCommandsList(array $commands): void
    {
        foreach ($commands as $command) {
            $this->quote($this->prepareOutput("  " . $command->command()), false);
            $this->output($this->prepareOutput($command->description()), "", "", false);

            if ($command->options() !== null) {
                $options = $this->stringfyOptions($command->options());
                $this->soft($this->prepareOutput($options, self::OPTIONS_SIZE), false);
            }

            echo "\n";
        }
    }

    /** @param array<string> $options */
    private function stringfyOptions(array $options)
    {
        $array = [];
        foreach ($options as $option => $descritpion) {
            $array[] = sprintf("[ %s ] %s", $option, $descritpion);
        }

        return implode(", ", $array);
    }

    private function prepareOutput(string $string, ?int $maxLenght = null): string
    {
        if (is_null($maxLenght)) {
            $maxLenght = self::STRLEN_SIZE;
        }

        if (strlen($string) > $maxLenght) {
            $string = substr($string, 0, $maxLenght - 3) . "...";
        }

        return sprintf("%s", str_pad($string, self::OUTPUT_SIZE));
    }

    private function dispatch(string $userCommand)
    {
        $command = $this->commands->findByName($userCommand);
        if ($command === null) {
            $this->error("Command {$userCommand} not found.");
            return;
        }

        if ($command->timeout() !== null) {
            set_time_limit($command->timeout());
        }

        try {
            $this->app->call($command->getCommand() . "@handler");
        } catch (\Exception $e) {
            $this->exceptionHandler->render($e);
        }

    }
}
