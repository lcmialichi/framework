<?php

namespace App\Console;

use App\FileSystem\Stream;
use App\Console\Bags\OptionBag;

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

    public function options()
    {
        return $this->options->options();
    }

    protected function outputSream(Stream $output): void
    {
        ob_start();
        while ($output->eof() === false) {
            echo $output->read();
            ob_flush();
        }
        ob_end_flush();
        $output->close();
    }

}
