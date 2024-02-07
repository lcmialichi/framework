<?php

namespace App\Console;

use App\FileSystem\Stream;
use App\Console\Features\ProgressBar;
use App\Container\ContainerInterface;

class Command extends Cli
{
    public function output(
        string $output,
        string $fHexa = "#FFFFFF",
        string $bHexa = "",
        bool $endLine = true
    ): void {
        $stream = new Stream;
        $stream->write(
            $this->stringfyOutput(
                $output,
                $bHexa,
                $fHexa,
                $endLine
            )
        );

        $this->outputSream($stream);
    }

    public function warning(string $output, $endLine = true)
    {
        $this->output($output, config("app.command.colors.warning"), "", $endLine);
    }
    public function error(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.error"), "", $endLine);
    }

    public function success(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.success"), "", $endLine);
    }

    public function info(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.info"), "", $endLine);
    }

    public function highlight(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.highlight"), "", $endLine);

    }

    public function notice(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.notice"), "", $endLine);
    }

    public function quote(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.quote"), "", $endLine);
    }

    public function soft(string $output, bool $endLine = true)
    {
        $this->output($output, config("app.command.colors.soft"), "", $endLine);
    }

    protected function container(): ContainerInterface
    {
        return resolve(ContainerInterface::class);
    }

    public static function argument(string|int $arg)
    {
        if (is_int($arg)) {
            return $_SERVER['argv'][$arg] ?? null;
        }

        return false;
    }

    public static function progressBar(): ProgressBar
    {
        return new ProgressBar();
    }
}

