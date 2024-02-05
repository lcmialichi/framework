<?php

namespace App\Http\Request;

use App\Routes\Route;
use App\Http\Request\Foundation\InputBag;
use App\Http\Request\Foundation\QueryBag;
use App\Http\Request\Foundation\ServerBag;

class Request implements RequestInterface
{
    private QueryBag $query;
    private InputBag $request;
    private $attributes;
    private $cookies;
    private $files;
    private ServerBag $server;
    private $content;
    private ?Route $routeResolver = null;

    public function __construct(
        array $query = [],
        array $request = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $server = [],
        $content = null
    ) {
        $this->query = new QueryBag($query);
        $this->request = new InputBag($request);
        $this->attributes = $attributes;
        $this->cookies = $cookies;
        $this->files = $files;
        $this->server = new ServerBag($server);
        $this->content = $content;
        if (!empty($headers = $this->server->getHeaders())) {
            if (isset($headers["CONTENT_TYPE"]) && $headers["CONTENT_TYPE"] === "application/json") {
                $this->request = new InputBag(
                    json_decode(
                        mb_convert_encoding($this->content, 'UTF-8'),
                        true
                    ) ?? []
                );
            }
        }
    }

    /**
     * Cria uma nova instanca de Request a partir das variaveis globais
     * 
     */
    public static function creacteFromGlobals(): static
    {
        return new static(
            query: $_GET,
            request: $_POST,
            attributes: [],
            cookies: $_COOKIE,
            files: $_FILES,
            server: $_SERVER,
            content: file_get_contents("php://input")
        );

    }

    public function all(): array
    {
        if (!empty($this->route())) {
            $routeParameters = $this->route()->parameters();
        }
        return array_merge($this->inputs(), $this->query(), $routeParameters ?? []);
    }

    /**
     * Retrna todos os inputs ou o input especificado
     * 
     */
    public function inputs($key = null): mixed
    {
        if ($key) {
            return dot($key, $this->request->all());
        }
        return $this->request->all();
    }

    /**
     * Retorna os Headers da requisição
     * 
     */
    public function getHeaders(): array
    {
        return $this->server->getHeaders();
    }

    public function header($key): mixed
    {
        return $this->server->getHeaders()[$key] ?? null;
    }

    private function server(): ServerBag
    {
        return $this->server;
    }

    /**
     * Retorna os query Params
     */
    public function query($key = null): mixed
    {
        $all = $this->query->all();
        if ($key) {
            return $all[$key] ?? null;
        }
        return $all;
    }

    public function setRouteResolver(Route $route): static
    {
        $this->routeResolver = $route;
        return $this;
    }

    public function getRouteResolver(): ?Route
    {
        return $this->routeResolver;
    }

    public function setUserResolver(\Closure $user): static
    {
        $this->userResolver = $user;
        return $this;
    }

    public function getUserResolver(): \Closure
    {
        return $this->userResolver ?? function () {
        };
    }

    /**
     * Retorna a rota ou um parametro da rota
     */
    public function route(): ?Route
    {
        $route = $this->getRouteResolver();
        return $route;
    }

    public function __get($key)
    {
        return $this->all()[$key] ?? $this->route()?->parameter($key);
    }

    public function validate(array $rules)
    {
        return validator($rules)->validate($this->all());
    }

    public function has(string $key): bool
    {
        return dot($key, $this->all()) != null;
    }

    public function getRequestMethod(): ?string
    {
        return $this->server->get("REQUEST_METHOD") ?? null;
    }
}
