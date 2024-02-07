<?php

use App\Configuration\Environment;
use App\Http\Request\RequestHandler;
use App\Event\EventHandler;
use App\Container\Container;
use Validators\Validator;

if (!function_exists("resolve")) {
    function resolve(string|callable $id, array $params = []): mixed
    {
        return container()->make($id, $params);
    }
}

if (!function_exists("container")) {
    function container($abstract = null)
    {
        if ($abstract) {
            return resolve($abstract);
        }

        return Container::getInstance();
    }
}

if (!function_exists('pascalCase')) {
    function pascalCase(string $string): string
    {
        if (preg_match("/[A-Z]*|[_-]/", $string)) {
            if (strtoupper($string) === $string) {
                $string = strtolower($string);
            }
        }

        $string = str_replace(["_", "-"], " ", trim(strtolower(macroCase($string))));
        $string = ucwords($string);
        $string = str_replace(" ", "", $string);
        return $string;
    }
}

if (!function_exists('camelCase')) {
    function camelCase(string $string): string
    {
        return lcfirst(pascalCase($string));
    }
}


if (!function_exists('snakeCase')) {
    function snakeCase(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
}


if (!function_exists('macroCase')) {
    function macroCase(string $string): string
    {
        $string = str_replace(["-", " "], "_", $string);
        if (strtoupper($string) === $string) {
            $string = strtolower($string);
        }
        return strtoupper(strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string)));
    }
}

if (!function_exists('resource')) {
    function resource(?string $resource = null): mixed
    {

        $baseNamespace = \App\Resources::class;
        $baseFilePath = __DIR__;

        if (!$resource) {
            return $baseFilePath;
        }

        $resourceClass = array_map(fn($item) => ucfirst($item), explode("/", $resource));
        $resourceClass = $baseNamespace . "\\" . implode("\\", $resourceClass);
        if (class_exists($resourceClass)) {
            return resolve($resourceClass);
        }

        $resource = $baseFilePath . "/" . $resource;
        if (is_dir($resource)) {
            return glob($resource . "/*.php");
        }
        if (file_exists($resource) || file_exists($resource = $resource . '.php')) {
            if (endsWith(".php", $resource)) {
                return require $resource;
            }
            return file_get_contents($resource);
        }

        return false;
    }
}

if (!function_exists('dd')) {
    function dd(...$var): void
    {
        foreach ($var as $item) {
            echo "<pre>";
            var_dump($item);
            echo "</pre>";
        }
        die();
    }
}


if (!function_exists('dot')) {
    function dot(string $search, array|object $array): mixed
    {
        if (array_key_exists($search, $array)) {
            return $array[$search];
        }
        if (!str_contains($search, '.')) {
            return $array[$search] ?? null;
        }

        foreach (explode('.', $search) as $segment) {
            if (is_object($array) and isset($array->{$segment})) {
                $array = $array->{$segment};
                continue;
            }
            if (array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return null;
            }
        }

        return $array;
    }
}

if (!function_exists('beginsWith')) {
    function beginsWith(string $search, string $string): mixed
    {
        return 0 === strncmp($search, $string, \strlen($search));
    }
}

if (!function_exists("endsWith")) {

    function endsWith(string $search, string $string): mixed
    {
        return substr($string, -strlen($search)) === $search;
    }
}

if (!function_exists("mask")) {
    function mask(string $string, string $pattern = "#")
    {

        $pattern = str_replace("#", "%s", $pattern);
        return sprintf($pattern, ...str_split($string));
    }
}

if (!function_exists("validator")) {
    function validator(array $rules = []): Validator
    {
        $validator = container()->make(Validator::class);
        $validator->setRules($rules);
        return $validator;
    }
}

if (!function_exists("config")) {
    function config(string $config): mixed
    {
        $config = explode(".", $config);
        $name = array_shift($config);
        $path = implode(".", $config);

        $baseFilePath = ROOT_PATH . "/config/{$name}.php";
        if (!file_exists($baseFilePath)) {
            return false;
        }

        if (!empty($path)) {
            return dot($path, require $baseFilePath);
        }

        return require $baseFilePath;
    }
}

if (!function_exists("composer")) {
    function composer(?string $key = null): mixed
    {
        $composer = file_get_contents(ROOT_PATH . "/composer.json");
        $composer = json_decode($composer, true);
        if (!$key) {
            return $composer;
        }
        return dot($key, $composer);
    }
}

if (!function_exists("event")) {

    function event(EventHandler $event): void
    {
        $publisher = new \App\Event\Publisher($event);
        $publisher->publish();
    }
}

if (!function_exists("environment")) {

    function environment(string $env): mixed
    {
        return Environment::make()->get($env);
    }
}

if (!function_exists("redirect")) {

    function redirect(string $name): void
    {
        container()->make(RequestHandler::class)->execute($name);
        die;
    }
}

if (!function_exists("lang")) {
    function lang(): mixed
    {
        return config("app.lang.default");
    }
}

if (!function_exists("trans")) {
    /** @todo add parameters functionality */
    function trans(string $key, array $params = [], string $lang = null): mixed
    {
        if ($lang === null) {
            $lang = lang();
        }

        $path = explode(".", $key);
        $file = array_shift($path);
        $path = implode(".", $path);

        $baseFilePath = ROOT_PATH . "/lang/{$lang}/{$file}.php";
        if (!file_exists($baseFilePath)) {
            return false;
        }

        if (!empty($path)) {
            return dot($path, require $baseFilePath);
        }

        return require $baseFilePath;
    }
}

