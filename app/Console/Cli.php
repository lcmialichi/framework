<?php

namespace App\Console;

use App\Console\Colors;
use App\Console\Bags\OptionBag;
use App\FileSystem\StreamInterface;

abstract class Cli
{
    private OptionBag $options;

    public function __construct(?OptionBag $options = null)
    {
        $this->options = $options ?? $this->getOptionsFromArgs();
    }

    private function getOptionsFromArgs(): OptionBag
    {
        return OptionBag::createFromArgs();
    }

    public function getCurrentUser(): string
    {
        return get_current_user();
    }

    public function isRuningFromTerminal()
    {
        return php_sapi_name() === "cli";
    }

    private function commandExists(string $command)
    {
        $whereIsCommand = (PHP_OS == 'WINNT') ? 'where' : 'which';
        $return = shell_exec(sprintf("%s %s", $whereIsCommand, escapeshellarg($command)));
        return !empty($return);
    }

    public function options()
    {
        return $this->options->options();
    }

    public function option(string|array $option): mixed
    {
        if (is_array($option)) {
            foreach ($option as $item) {
                $fromBag[$item] = $this->options->option($item);
            }

            return $fromBag;
        }

        return $this->options->option($option);
    }
    
    protected function outputSream(StreamInterface $output): void
    {
        if (!$output->isReadable()) {
            throw new \RuntimeException('Output stream is not readable');
        }

        ob_start();
        $output->rewind();
        while ($output->eof() === false) {
            echo $output->read();
            ob_flush();
        }
        ob_end_flush();
    }

    public function waitForInteraction(int $timeout = 5): string|bool
    {
        $fd = fopen('php://stdin', 'r');
        $read = array($fd);
        if (stream_select($read, $write, $except, $timeout)) {
            return trim(fgets($fd));
        }

        return false;

    }

    public function overwrite(): void
    {
        echo "\r\033[K";
    }


    public function removeLastLine()
    {
        echo "\r\x1b[K";
        echo "\033[1A\033[K";
    }

    private function getRGB(string $hex): string
    {
        return Colors::fromHexadecimal($hex)->toRGB();
    }

    public function stringfyOutput(
        string $output,
        string $bHexa = "#FF55FF",
        string $fHexa = "#000000",
        bool $endLine = true

    ) {
        return sprintf(
            "\033[48;2;%sm\033[38;2;%sm%s\033[0m%s",
            $this->getRGB($bHexa),
            $this->getRGB($fHexa),
            $output,
            $endLine ? PHP_EOL : ""
        );
    }
}
