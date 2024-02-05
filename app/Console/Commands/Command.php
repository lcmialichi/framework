<?php

namespace App\Console\Commands;

use Exception;
use Throwable;
use App\Console\Displayer;
use App\Container\Container;
use App\Helpers\ClassLoader;
use App\Contracts\ExceptionHandlerInterface;

class Command
{
    /**
     * @var boolean
     */
    private bool $isLoaded = false;

    /**
     * # var array
     */
    private $commands = [];

    public function __construct(
        private ClassLoader $loader,
        private Displayer $console
    ) {
    }

    private function load()
    {
        foreach ($this->loader->load() as $class) {
            $command = $this->getCommand($class);
            if ($command) {
                $this->commands[$class] = [
                    "commands" => array_map(
                        fn($c) => trim($c),
                        explode("|", $command)
                    ),
                    "class" => $class,
                    "description" => $this->getDescription($class),
                    "options" => $this->getOptions($class),
                    "timeout" => $this->getTimeOut($class) ?? null,
                    "instances" => $this->getInstances($class)
                ];
            }
        }

        $this->isLoaded = true;
    }

    public function get(string $command)
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        return array_filter($this->commands, function ($item) use ($command) {
            return array_key_exists($command, array_flip($item["commands"]));
        });
    }

    private function getCommand(string $class)
    {
        return $this->getProperty($class, "command");
    }

    private function getDescription(string $class)
    {
        return $this->getProperty($class, "description");
    }

    private function getOptions(string $class)
    {
        return $this->getProperty($class, "options");
    }

    private function getTimeOut(string $class)
    {
        return $this->getProperty($class, "timeout");
    }

    private function getInstances(string $class)
    {
        return $this->getProperty($class, "instances");
    }

    private function getProperty($class, $property)
    {
        $reflection = new \ReflectionClass($class);
        if ($reflection->hasProperty($property)) {
            try {
                $reflectionProp = $reflection->getProperty($property);
                $reflectionProp->setAccessible(true);

                return $reflectionProp->getValue(
                    $reflection->newInstanceWithoutConstructor()
                );
            } catch (\Error) {
                return false;
            }
        }

        return false;
    }


    public function commands()
    {
        if (!$this->isLoaded) {
            $this->load();
        }
        return $this->commands;
    }

    public function outputList()
    {
        foreach ($this->commands() as $comand) {
            $commands = implode(" | ", $comand["commands"]);
            $this->console->output(
                $commands,
                "cyan",
                "black",
                false
            );

            $this->console->output(
                $size = str_repeat(" ", 25 - strlen($commands)) . sprintf(" - %s", $comand["description"]),
                "white",
                "black",
                false
            );

            if ($comand["options"]) {
                foreach ($comand["options"] as $option => $description) {
                    $this->console->output(
                        " {$option}: {$description}.",
                        "grey",
                        "black",
                        false
                    );
                }
            }
            $this->console->output("", "white", "black");
        }
    }

    public function run(string $call)
    {
        try {
            $command = $this->get($call);
            if (count($command) == 0) {
                throw new Exception("Comando '{$call}' nao existe!");
            }

            $class = key($command);
            if ($command[$class]["timeout"]) {
                set_time_limit($command[$class]["timeout"]);
            }

            if ($command[$class]["instances"] !== false) {
                $this->breakIfHasInstances($call, $command[$class]["instances"]);
            }

            $this->execute($class);

        } catch (Throwable $e) {
            resolve(
                ExceptionHandlerInterface::class
            )->render($e);

        }
    }

    public function breakIfHasInstances(string $call, int $instances)
    {
        if (
            $this->console->console()->exec(
                "ps aux | grep {$call} | grep -v grep | wc -l"
            ) > $instances
        ) {
            throw new Exception("Já existem {$instances} intancias em execução!");
        }
        ;
    }

    private function execute(string $class)
    {
        Container::getInstance()->call($class . "@handler");
    }
}
