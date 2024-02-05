<?php

namespace App\ORM\Connection\Group;

use App\Configuration\Environment;
use App\ORM\Connection\Group\ConnectionGroup;

class DefaultConnection implements ConnectionGroup
{  

    public function driver(): string
    {
        return Environment::make()->get("DB_DRIVER") ?? "";
    }

    public function host(): string
    {
        return  Environment::make()->get("DB_HOST") ?? "";
    }

    public function port(): int
    {
        return (int)  Environment::make()->get("DB_PORT") ?? "";
    }

    public function user(): string
    {
        return  Environment::make()->get("DB_USER") ?? "";
    }

    public function password(): string
    {
        return  Environment::make()->get("DB_PASSWORD") ?? "";
    }

    public function database(): string
    {
        return  Environment::make()->get("DB_DATABASE") ?? "";
    }

}
