<?php

namespace App\Routes;

use App\Routes\Group;
use App\Routes\Route;
use App\Routes\Enums\HttpCode;
use App\Container\ContainerInterface;
use App\Http\Request\RequestInterface;
use App\Routes\Collections\GroupCollection;
use App\Routes\Collections\RouteCollection;
use App\Routes\Middleware\MiddlewareHandler;

class Dispatcher
{
    private HttpCode $httpCode;

    public function __construct(
        private ContainerInterface $container,
        private RequestInterface $request,
    ) {
        $this->httpCode = HttpCode::OK;
    }

    private function getHttpRoute(): string
    {
        return $this->request->query("route") ?? "/";
    }

    private function getHttpMethod(): string
    {
        return $this->request->getRequestMethod();
    }

    public function dispatch(): mixed
    {
        return $this->dispatchByUrl($this->getHttpRoute());
    }

    public function redirect(string $name): mixed
    {
        return $this->dispatchByName($name);
    }

    private function resolveGroupIfAssigned(
        ?string $url,
        ?string &$name = null,
        ?array &$routes = null,
        ?Group &$group = null
    ): void {

        foreach ($this->getDefinedGroups() as $routeGroup) {
            $hasOnGroup = $url && $this->hasOnGroup($url, $routeGroup);
            $hasOnGroupNamed = $name && $this->hasOnGroupNamed($name, $routeGroup);

            if ($hasOnGroup || $hasOnGroupNamed) {
                $group = $routeGroup;
                $routes = $group?->resolveRoutes()?->getRoutes();
                $name = $group?->getName();
            }
        }
    }

    /**
     * @param array<Route> $routes
     */
    private function resolveRoute(
        array $routes,
        ?Group $group,
        string $url
    ): mixed {
        foreach ($routes as $route) {
            $prefix = $group?->getPrefix() ?? $route?->getPrefix();
            $routePath = $prefix . $route->getRoute();
            $parameters = $this->routeMatches($routePath, $url);
            if ($parameters !== false) {
                $this->updateRequestParameters($route, $parameters);
                return $this->execute($route, $group);
            }
        }

        $this->setHttpCode(HttpCode::NOT_FOUND);
        return false;
    }

    private function dispatchByName(string $name)
    {
        $name = explode(".", $name);
        $this->resolveGroupIfAssigned(null, $name[0], $routes, $group);
        $routes = $routes ?? $this->getDefinedRoutes();
        $routes = $routes['GET'] ?? []; /** for a wile it will be only availible for GET HTTP Method */

        if (empty($routes)) {
            $this->setHttpCode(HttpCode::NOT_FOUND);
            return false;
        }

        foreach ($routes as $route) {
            if ($route->getName() == ($name[1] ?? $name[0])) {
                return $this->execute($route, null);
            }
        }

        $this->setHttpCode(HttpCode::NOT_FOUND);
        return false;
    }

    private function dispatchByUrl(string $url)
    {
        $this->resolveGroupIfAssigned($url, $routes, $group);
        $routes = $routes ?? $this->getDefinedRoutes();
        $routes = $routes[$this->getHttpMethod()] ?? [];

        if (empty($routes)) {
            $this->setHttpCode(HttpCode::NOT_FOUND);
            return false;
        }

        return $this->resolveRoute($routes, $group, $url);
    }

    private function execute(Route $route, ?Group $group): mixed
    {
        $middlewares = $this->prepareMidlewares($route, $group);
        $middlewares->before();
        $handler = $route->getHandler();

        if (is_string($handler)) {
            $response = $this->container->call($route->getHandler());
        }

        if (is_callable($handler)) {
            $response = $this->container->make($handler(...));
        }

        $middlewares->after();
        return $response;
    }

    private function prepareMidlewares(Route $route, ?Group $group): MiddlewareHandler
    {
        $middlewares = array_merge(
            $route->getMiddleware() ?? [],
            $group?->getMiddleware() ?? []
        );

        $handler = new MiddlewareHandler($middlewares);
        $handler->prepare();
        return $handler;
    }

    private function routeMatches(string $route, string $url): false|array
    {
        $keys = $this->getRouteParameters($route);
        if ($route == $url) {
            return [];
        }

        $parameters = $this->getUrlParameters($keys, $route, $url);
        if ($route != $url || $url == "/") {
            return false;
        }
        return $parameters;
    }

    private function getUrlParameters(array $keys, string &$route, string $url): array
    {
        $params = [];
        $diff = array_values(array_diff_assoc(explode("/", $url), explode("/", $route)));
        for ($offset = 0; $offset < count($keys); $offset++) {
            $params[$keys[$offset][1]] = $diff[$offset] ?? null;
            $route = str_replace($keys[$offset][0], $diff[$offset] ?? "", $route);
        }
        return $params;
    }

    private function getRouteParameters(string $route): array
    {
        preg_match_all(
            "~\{\s* ([a-zA-Z_][a-zA-Z0-9_-]*) \}~x",
            $route,
            $keys,
            PREG_SET_ORDER
        );

        if (empty($keys)) {
            return [];
        }

        return $keys;
    }

    private function updateRequestParameters(Route $route, array $requestParameters): void
    {
        $route->setParameters($requestParameters);
        $this->container->bind(
            RequestInterface::class,
            fn() => $this->request->setRouteResolver($route)
        );
    }

    private function hasOnGroup(string $route, Group $group)
    {
        return str_starts_with($route, $group->getPrefix()) && $group->getPrefix() !== "";
    }

    private function hasOnGroupNamed(string $name, Group $group)
    {
        return $name == $group->getName() && !empty($group->getName());
    }

    private function getDefinedGroups(): array
    {
        return GroupCollection::getGroups();
    }

    private function getDefinedRoutes(): array
    {
        return RouteCollection::getStaticRoutes();
    }

    private function setHttpCode(HttpCode $httpCode): void
    {
        $this->httpCode = $httpCode;
    }

    public function getHttpCode(): HttpCode
    {
        return $this->httpCode;
    }

    public function failed(): bool
    {
        return $this->getHttpCode()->value > 400;
    }

}
