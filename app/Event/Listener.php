<?php

namespace App\Event;

interface Listener
{
    public function name(): string;
    public function handle(array $payload): void;
}
