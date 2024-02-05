<?php

namespace App\Event\Connection;

interface Connection
{
    public function user(): string;

    public function password(): string;

    public function host(): string;

    public function port(): int;

}
