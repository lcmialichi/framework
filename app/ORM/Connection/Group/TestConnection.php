<?php

namespace App\ORM\Connection\Group;

use App\Configuration\Environment;

class TestConnection implements ConnectionGroup
{
    public function driver(): string
    {
        return Environment::make()->get("DB_TEST_DRIVER") ?? "";
    }

    public function host(): string
    {
        return Environment::make()->get("DB_TEST_HOST") ?? "";
    }

    public function port(): int
    {
        return (int) Environment::make()->get("DB_TEST_PORT") ?? "";
    }

    public function user(): string
    {
        return Environment::make()->get("DB_TEST_USER") ?? "";
    }

    public function password(): string
    {
        return Environment::make()->get("DB_TEST_PASSWORD") ?? "";
    }

    public function database(): string
    {
        return Environment::make()->get("DB_TEST_DATABASE") ?? "";
    }
}
