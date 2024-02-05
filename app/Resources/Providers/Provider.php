<?php

namespace App\Resources\Providers;

use App\Container\ContainerInterface;

abstract class Provider {

    final public function __construct(public ContainerInterface $app){
    }

    public abstract function register(): void;
    
}