<?php

namespace App\Routes;

use App\Routes\Route;
use App\Routes\Enums\Method;

class Actions
{
    public function get(string $route, string|callable $resolver): Route
    {
        return Route::make(Method::GET, $route, $resolver);
    }

    public function post(string $route, string|callable $resolver): Route
    {
        return Route::make(Method::POST, $route, $resolver);
    }

    public function put(string $route, string|callable $resolver): Route
    {
        return Route::make(Method::PUT, $route, $resolver);
    }

    public function delete(string $route, string|callable $resolver): Route
    {
        return Route::make(Method::DELETE, $route, $resolver);
    }

    public function patch(string $route, string|callable $resolver): Route
    {
        return Route::make(Method::PATCH, $route, $resolver);
    }

    public function options(string $route, string|callable $resolver): Route
    {
        return Route::make(Method::OPTIONS, $route, $resolver);
    }

    public static function make(): self
    {
        return new self();
    }
}
