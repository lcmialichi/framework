<?php



namespace App\Routes;

use App\Routes\Enums\Method;
use App\Routes\Components\Name;
use App\Routes\Components\Prefix;
use App\Routes\Components\Middleware;

class Route
{
    use Middleware, Name, Prefix;

    private string $route;
    private Method $method;
    private $handler;
    private array $parameters;

    public function __construct(
        Method $method,
        string $route,
        string|callable $handler,
        array $parameters = []
    ) {
        $this->method = $method;
        $this->route = $route;
        $this->handler = $handler;
        $this->parameters = $parameters;
    }

    public static function make(
        Method $method,
        string $route,
        string|callable $handler,
        array $parameters = []
    ): self {
        return new self($method, $route, $handler, $parameters);
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getMethod(): Method
    {
        return $this->method;
    }

    public function getHandler(): string|callable
    {
        return $this->handler;
    }

    public function parameters(): array
    {
        return $this->parameters;
    }

    public function parameter(string $param)
    {
        return $this->parameters[$param] ?? null;

    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

}
