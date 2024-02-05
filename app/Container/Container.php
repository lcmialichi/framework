<?php

namespace App\Container;

use ReflectionParameter;
use App\Container\ContainerInterface;

class Container implements ContainerInterface
{

    /**
     * @var array
     */
    private array $container = [];
    /**
     * @var self $intance
     */
    private static ?self $instance = null;

    /**
     *
     * @var array
     */
    private $extends = [];

    /**
     * vincula um callback a uma classe 
     * 
     */
    public function bind(string $id, callable $concrete): void
    {
        $this->container[$id] = $concrete;
    }

    /**
     * Retorna todos os vinculos dentro do container
     * 
     */
    public function allBinding(): array
    {
        return $this->container;
    }

    /**
     * Resolve classe e metodo
     * ex Classe@metodo
     * @throws \RuntimeException
     */
    public function call(string $classMethod, array $parameters = [])
    {
        $items = explode("@", $classMethod);
        if (count($items) != 2) {
            throw new \RuntimeException("formato nao suportado pelo container!");
        }
        $callback = ($this->make($items[0]))->{$items[1]}(...);
        return $this->make($callback, $parameters);
    }

    /**
     * Remove um serviço atrelado a uma classe
     */
    public function remove($id): bool
    {

        if (isset($this->container[$id])) {
            unset($this->container[$id]);
            return true;
        }

        return false;
    }

    /**
     * Verifica se existe um serviço atrelado a uma classe
     */
    public function has($id): bool
    {
        return isset($this->container[$id]);
    }

    /**
     * Coleta o serviço atrelado a classe
     */
    public function get($id): callable|null
    {
        return $this->container[$id] ?? null;
    }

    /**
     * Resolve dependencia em cascata
     * @throws \RuntimeException
     */
    public function make(string|callable $id, array $params = []): mixed
    {
        if (is_callable($id)) {
            return $this->resolveCallable($id, $params);
        }

        if ($this->has($id)) {
            return $this->get($id)($this);
        }

        if (!class_exists($id)) {
            throw new \RuntimeException(sprintf("'%s' nao pode ser resolvido", $id));
        }

        $reflection = new \ReflectionClass($id);
        if (!$reflection->isInstantiable()) {
            throw new \RuntimeException(sprintf("'%s' nao pode ser instanciada", $id));
        }

        if (!$this->hasParams($id) && $reflection->isInstantiable()) {
            $instance = new $id;
        }

        $instance =  $instance ?? $this->resolveInstantiable($id, $params);
        if ($this->hasAfterFire($id)) {
            $instance = $this->resolveAfterFire($id, $instance) ?? $instance;
        }
        return $instance;
    }

    /**
     * @param string $id
     * @param array $params
     */
    private function resolveInstantiable(string $id, array $params = [])
    {
        $resolved = $this->resolveDependencyList(
            $this->constructorParameters($id),
            new ParameterBag($params)
        );
        return new $id(...$resolved ?? []);
    }

    /**
     * @param Closure $callback
     * @param array $params
     */
    private function resolveCallable(callable $callable, array $params = [])
    {
        $resolved = $this->resolveDependencyList(
            $this->closureParameters($callable),
            new ParameterBag($params)
        );
        return $callable(...$resolved ?? []);
    }

    /**
     * @param ReflectionParameter[] $dependencies
     * @param ParameterBag $params
     */
    private function resolveDependencyList(array $dependencies, ParameterBag $params): array
    {
        return array_map(
            function (ReflectionParameter $dependency) use ($params) {
                return $this->resolveDependency(new Dependency($dependency), $params);
            },
            $dependencies
        );
    }

    /**
     * @param Dependency $dependency
     * @param ParameterBag $params
     * @throws \RuntimeException
     */
    private function resolveDependency(Dependency $dependency, ParameterBag $params)
    {
        if ($params->has($name = $dependency->getName())) {
            return $params->get($name);
        }
        if (!$dependency->isNamedType()) {
            throw new \RuntimeException(sprintf("'%s' nao pode ser resolvido", $name));
        }
        if ($dependency->hasDefaultValue()) {
            return $dependency->getDefaultValue();
        }
        return $this->make($dependency->getTypeHint());
    }

    /**
     * Verifica se a classe tem parametros no construtor
     * 
     * @param object|string $class
     */
    private function hasParams(object|string $class): bool
    {
        $reflector = new \ReflectionClass($class);
        $constructor = $reflector->getConstructor();
        return $constructor !== null && $constructor->getNumberOfParameters() > 0;
    }

    private function closureParameters(callable $callable): array
    {
        return (new \ReflectionFunction($callable))->getParameters();
    }

    /**
     * @param string $entity
     * @return ReflectionParameter[]
     */
    private function constructorParameters(string $entity): array
    {
        $constructor = (new \ReflectionClass($entity))->getConstructor();
        if (!$constructor) {
            return [];
        }

        return $constructor->getParameters();
    }

    public static function getInstance(): static
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }
    /**
     * Seta um callback para ser executado em cima da instancia resolvida antes de retorna-la
     *
     * @param string $id nome da classe abstrata
     * @param \Closure $extends
     * @return void
     */
    public function setAfterFire(string $id, \Closure $extends): void
    {
        $class = new \ReflectionClass($id);
        if (!$class->isAbstract()) {
            throw new \RuntimeException(sprintf("'%s' deve ser uma", $id));
        }
        $this->extends[$id] =  $extends;
    }

    public function hasAfterFire(string $id): bool
    {
        return isset($this->extends[get_parent_class($id)]);
    }

    public function getAfterFire(string $id): null|callable
    {
        return $this->extends[get_parent_class($id)] ?? null;
    }

    public function resolveAfterFire(string $id, object $instance): mixed
    {
        $abstract = $this->getAfterFire($id);
        if(is_callable($abstract)){
            return $abstract($instance, $id);
        }

        return null;
    }
}
