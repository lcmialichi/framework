<?php



namespace App\Routes\Middleware;

interface Middleware
{
    public function before(): void;

    public function after(): void;
}
