<?php

namespace App\ORM\Connection\Group;

use App\Configuration\Environment;

interface ConnectionGroup
{
    public function driver(): string;

    public function host(): string;

    public function port(): int;

    public function user(): string;

    public function password(): string;

    public function database(): string;
}
