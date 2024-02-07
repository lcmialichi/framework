<?php

use App\Container\Container;
use App\Http\Request\Request;
use App\Container\ContainerInterface;
use App\Http\Request\RequestInterface;
use App\ORM\Connection\Group\ConnectionGroup;
use App\ORM\Connection\Group\DefaultConnection;
use App\Event\Connection\Adapter\AMQPConnection;

/*
|--------------------------------------------------------------------------
| Application boot
|--------------------------------------------------------------------------
*/

if (!defined("ROOT_PATH")) {
    define("ROOT_PATH", __DIR__ . "/..");
}

$container = Container::getInstance();
$container->bind(
    ContainerInterface::class,
    fn() => Container::getInstance()
);

$container->bind(
    RequestInterface::class,
    fn() => Request::creacteFromGlobals()
);

$container->bind(
    App\Contracts\ExceptionHandlerInterface::class,
    fn() => new App\Exception\Handler
);

$container->bind(
    ConnectionGroup::class,
    fn($app) => $app->make(DefaultConnection::class)
);

$container->bind(
    App\Event\Connection\Connection::class,
    fn() => new AMQPConnection()
);

return $container;
