<?php

namespace App\Console\Bags;

class OptionBag
{
    public function __construct(private array $options = [])
    {
    }

    public function option(string $option)
    {
        if ($this->has($option)) {
            return $this->options[$option];
        }

        return null;
    }

    public function options(): array
    {
        return $this->options;
    }

    public function has($option): bool
    {
        return isset($this->options[$option]);
    }

    public static function createFromArgs()
    {
        foreach ($_SERVER['argv'] as $args) {
            if (beginsWith("--", $args)) {
                $arg = explode("=", $args);
                $option[substr($arg[0], 2)] = $arg[1] ?? true;
            } elseif (beginsWith("-", $args)) {
                $arg = explode("=", substr($args, 1));
                if (strlen($arg[0]) == 1) {
                    $option[$arg[0]] = $arg[1] ?? true;
                }
            }
        }

        return new OptionBag($option ?? []);
    }
}
