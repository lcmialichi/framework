<?php

namespace App\Console\Commands;

use App\Console\Command;
use App\Console\Features\ProgressBar;

class TestCommand extends Command
{
    private string $command = "test";
    private string $description = "command built to test the console";

    public function handler(): void
    {
       $this->warning("Warning!");
       $this->error("error!");
       $this->success("success!");
       $this->notice("notice!");
       $this->info("info!");

       $p = new ProgressBar();

       $p->start();
       for($i = 0; $i < 100; $i++){
           $p->increment();
           sleep(1);
       }

         $p->finish();
    }
    
    
}
