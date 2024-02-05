<?php

namespace App\Routes;

use App\Routes\Route;
use App\Routes\Actions;
use App\Routes\Collections\GroupCollection;
use App\Routes\Collections\RouteCollection;

/**
 * @method static Route get(string $route, callable|string $action)
 * @method static Route post(string $route, callable|string $action)
 * @method static Route put(string $route, callable|string $action)
 * @method static Route delete(string $route, callable|string $action)
 * @method static Route patch(string $route, callable|string $action)
 * @method static Route options(string $route, callable|string $action)
 */
class Router
{
    private RouteCollection $collection;

    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    public static function __callStatic($method, $args)
    {
        $route = Actions::make()->$method(...$args);
        RouteCollection::addStaticRoute($route);
        return $route;
    }

    public function __call($method, $args)
    {
        $route = Actions::make()->$method(...$args);
        $this->collection->addRoute($route);
        return $route;
    }

    public static function group(string $name, callable $resolver)
    {  
        $group = Group::make($name, $resolver);
        GroupCollection::addGroup($group);
        return $group;
    }

    public function &getRoutesCollection(): RouteCollection
    {
        return $this->collection;
    }

    public static function make(): self
    {
        return new self(new RouteCollection);
    }

}
