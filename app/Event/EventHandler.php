<?php

namespace App\Event;

abstract class EventHandler
{
    public abstract function channels(): array;

    public abstract function payload(): array;

}
