<?php



namespace App\Routes\Middleware;

use App\Routes\Group;
use App\Routes\Route;
use App\Routes\Middleware\Middleware;

class MiddlewareHandler
{
    private array $middlewaresSolved = [];

    public function __construct(private array $middlewares)
    {
    }

    public function prepare(): void
    {
        foreach ($this->middlewares as $middleware) {
            if (is_callable($middleware)) {
                $this->middlewaresSolved[] = $middleware;
            }

            if (is_string($middleware)) {
                if (!class_exists($middleware)) {
                    throw new \Exception(sprintf("Middleware %s not found", $middleware));
                }
                $middleware = resolve($middleware);
                if (!$middleware instanceof Middleware) {
                    throw new \Exception(sprintf("Middleware %s must implement %s", $middleware::class, Middleware::class));
                }

                $this->middlewaresSolved[] = $middleware;
            }
        }
    }

    public function before(): void
    {
        foreach ($this->middlewaresSolved as $middleware) {
            if (is_callable($middleware)) {
                $middleware();
                continue;
            }

            $middleware->before();
        }
    }

    public function after(): void
    {
        foreach ($this->middlewaresSolved as $middleware) {
            if (is_callable($middleware)) {
                continue;
            }

            $middleware->after();
        }
    }
}
