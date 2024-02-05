<?php

namespace App\Console\Commands;

use App\Routes\Route;
use App\Routes\Router;
use App\Console\Displayer;
use App\Routes\Collections\RouteCollection;

class TestCommand extends Displayer
{
    private string $command = "test";
    private string $description = "command built to test the console";

    public function handler(): void
    {
        Router::post();
        Router::put();
        Router::delete();
        
        $group = Router::group("teste", function ($router) {
            $router->get();
            $router->post();
            $router->put();
            $router;
        })->middleware("teste");
        
        dd($group, RouteCollection::$STATIC_ROUTES);

        
    }
}
