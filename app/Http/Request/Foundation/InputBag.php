<?php

namespace App\Http\Request\Foundation;

class InputBag {

    public function __construct( private array $parameters = []) {
    }

    public function all() : array {
        return $this->parameters ?? [];
    }

    public function __get($key){
        return $this->parameters[$key];
    }
}