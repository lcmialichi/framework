<?php

namespace App\Routes\Collections;

use App\Routes\Group;
use App\Routes\Route;

class RouteCollection
{
    public array $routes = [];

    public static $STATIC_ROUTES = [];

    public function addRoute(Route &$route): Route
    {
        $this->routes[$route->getMethod()->value()][] = $route;
        return $route;
    }

    public static function addStaticRoute(Route &$route): Route
    {
        self::$STATIC_ROUTES[$route->getMethod()->value()][] = $route;
        return $route;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public static function getStaticRoutes(): array
    {
        return self::$STATIC_ROUTES;
    }



}
