<?php

namespace App\Console\Execution;

use App\Console\Console;
use App\Console\Displayer;

class CommandEmiter extends Displayer
{
    private ?string $currentExecution = null;

    public function __construct()
    {
        parent::__construct(new Console); /** @todo this will be diferent on future */
        $this->currentExecution = $this->getCommandFromInput();
        $this->loadCommands();
    }


    public function getCommandFromInput()
    {
        return $this->console()->argument(1);
    }

    public function loadCommands(): void
    {
        
    }


    public function emit(string $command): void
    {

    }
}
