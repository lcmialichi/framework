<?php

namespace App\Console\Commands;

use App\Console\Displayer;

class TestCommand extends Displayer
{
    private string $command = "test";
    private string $description = "command built to test the console";

    public function handler(): void
    {
        //
    }
    
}
