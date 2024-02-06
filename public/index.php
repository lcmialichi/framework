<?php

use App\Http\Request\RequestHandler;

require __DIR__ . "/../vendor/autoload.php";

$container = require __DIR__ . "/../boot/Application.php";

$container->make(RequestHandler::class)->execute();
