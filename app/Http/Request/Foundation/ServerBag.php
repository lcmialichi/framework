<?php

namespace App\Http\Request\Foundation;

class ServerBag {

    public function __construct( private array $parameters = []) {
      
    }

    public function all() : array {
        return $this->parameters;
    }

    public function get($key){
        return $this->parameters[$key];
    }

    public function getHeaders() : array{

        foreach($this->parameters as $key => $value){
            if(beginsWith("HTTP_", $key)){
                $headers[$key] = $value;
            }
            if(in_array($key, ["CONTENT_TYPE", "CONTENT_LENGTH", "CONTENT_MD5"])){
                $headers[$key] = $value;
            }
        }

        return $headers ?? [];
    }

}