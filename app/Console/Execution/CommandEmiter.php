<?php

namespace App\Console\Execution;

use App\Console\Console;
use App\Console\Displayer;
use App\Helpers\ClassLoader;
use App\Contracts\ExceptionHandlerInterface;
use App\Console\Execution\CommandsCollection;

class CommandEmiter extends Displayer
{
    private ?string $currentExecution = null;
    private const COMMAND_SIZE = 35;
    private const OPTIONS_SIZE = 35;

    private CommandsCollection $commands;

    public function __construct()
    {
        parent::__construct(new Console); /** @todo this will be diferent on future */
        $this->currentExecution = $this->getCommandFromInput();
        $this->commands = $this->loadCommands();
    }

    public function getCommandFromInput()
    {
        return $this->console()->argument(1);
    }

    public function getCommandsNamespace(): string
    {
        return config("app.command.namespace");
    }

    public function loadCommands()
    {
        $loader = new ClassLoader($this->getCommandsNamespace(), true);
        $commands = $loader->load();
        $collection = [];
        foreach ($commands as $command) {
            $commandInstance = new Command($command);
            $name = $commandInstance->command();
            if ($name === null) {
                $this->warning("Warning: Command {$command} does not have a command property. Skipping...");
                continue;
            }

            $section = "general";
            if (str_contains($name, ":")) {
                $section = explode(":", $name)[0];
            }

            $collection[$section][$commandInstance->command()] = $commandInstance;
        }

        return new CommandsCollection($collection);
    }

    public function emit(?string $command = null): void
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

    /** @param array<Command> $commands */
    private function showCommandsList(array $commands): void
    {
        foreach ($commands as $command) {
            $this->output(sprintf("  %s", str_pad($command->command(), self::COMMAND_SIZE)), "cyan", "balck", false);
            $this->output(sprintf("%s", str_pad($command->description(), self::OPTIONS_SIZE)), "white", "black", false);

            if ($command->options() !== null) {
                $this->output(str_pad($this->stringfyOptions($command->options()), self::OPTIONS_SIZE), "white", "grey", false);
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
            $handler = resolve($command->getCommand())->handler(...);
            resolve($handler);
        } catch (\Exception $e) {
             resolve(
                ExceptionHandlerInterface::class
            )->render($e);
        }

    }
}
