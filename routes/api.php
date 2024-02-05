<?php

use App\Routes\Router;

Router::get("/", App\Controller\ThisIsATestController::class . "@index");

