<?php

namespace App\Container;

interface ContainerInterface
{
    public function bind(string $id, callable $concrete): void;
    public function allBinding(): array;
    public function call(string $classMethod, array $parameters = []);
    public function remove($id): bool;
    public function has($id): bool;
    public function get($id): callable|null;
    public function make(string|callable $id, array $params = []): mixed;
    public static function getInstance(): static;
    public function setAfterFire(string $id, \Closure $extends): void;
    public function hasAfterFire(string $id): bool;
    public function getAfterFire(string $id): null|callable;
    public function resolveAfterFire(string $id, object $instance): mixed;

}
