<?php

namespace App\Console\Execution;

class Command
{
    public function __construct(
        private string $command
    ) {
    }

    public function command(): ?string
    {
        return $this->getProperty("command");
    }

    public function description(): ?string
    {
        return $this->getProperty("description");
    }

    public function options(): ?array
    {
        return $this->getProperty("options");
    }

    public function instances(): ?array
    {
        return $this->getProperty("instances");
    }

    public function timeout(): ?int
    {
        return $this->getProperty("timeout");
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    private function getProperty(string $property): mixed
    {
        
        $reflection = new \ReflectionClass($this->command);
        if (!$reflection->hasProperty($property)) {
            return null;
        }

        try {
            $reflectionProp = $reflection->getProperty($property);
            $reflectionProp->setAccessible(true);

            return $reflectionProp->getValue(
                $reflection->newInstanceWithoutConstructor()
            );
        } catch (\Error) {
            return null;
        }

    }
}

