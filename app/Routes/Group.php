<?php



namespace App\Routes;

use App\Routes\Components\Middleware;
use App\Routes\Collections\RouteCollection;
use App\Routes\Components\Prefix;

class Group
{
    use Middleware, Prefix;

    private string $name;
    private $resolver;

    public function __construct(string $name, $resolver)
    {
        $this->name = $name;
        $this->resolver = $resolver;
    }

    public static function make(string $name, $resolver): self
    {
        return new self($name, $resolver);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getResolver(): callable
    {
        return $this->resolver;
    }

    public function resolveRoutes()
    {
        $router = new Router(new RouteCollection);
        \Closure::fromCallable($this->resolver)->call($router, $router);
        return $router->getRoutesCollection();
    }


}
