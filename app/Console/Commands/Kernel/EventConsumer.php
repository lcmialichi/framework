<?php

namespace App\Console\Commands\Kernel;

use App\Event\Consumer;
use App\Console\Command;
use App\Console\Displayer;
use App\Helpers\ClassLoader;

class EventConsumer extends Command
{   
    private string $command = "event:listen";
    private string $description = "listen to events queue"; 

    public function handler(Consumer $consumer): void
    {
        $this->warning("Listening to events queue");
        foreach($this->getChannels() as $channel) {
            $consumer->add(resolve($channel));
        }

        $consumer->listen();
    }

    private function getChannels(): array
    {
        $loader = new ClassLoader(config("events.listeners.namespace"));
        return $loader->load();
    }
}
