<?php

namespace App\Http\Request;

use App\Routes\Route;

interface RequestInterface
{
    public static function creacteFromGlobals(): static;
    public function all(): array;
    public function inputs($key = null): mixed;
    public function getHeaders(): array;
    public function header($key): mixed;
    public function query($key = null): mixed;
    public function setRouteResolver(Route $route): static;
    public function setUserResolver(\Closure $user): static;
    public function getRouteResolver(): ?Route;
    public function route(): ?Route;
    public function has(string $key): bool;
    public function getRequestMethod(): ?string;
    public function __get($key);

}
