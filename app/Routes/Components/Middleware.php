<?php

namespace App\Routes\Components;

trait Middleware
{
    /** @var string|callable $middleware */
    private $middleware = null;

    public function middleware(string|callable|array $middleware)
    {
        if (is_string($middleware) || is_callable($middleware)) {
            $middleware = [$middleware];
        }
        $this->middleware = $middleware;
        return $this;
    }

    public function getMiddleware(): null|string|callable|array
    {
        return $this->middleware;
    }
}
