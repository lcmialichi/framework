<?php



namespace App\Routes\Enums;

enum Method: string
{
    case GET = "GET";
    case POST = "POST";
    case PUT = "PUT";
    case PATCH = "PATCH";
    case DELETE = "DELETE";
    case OPTIONS = "OPTIONS";
    case HEAD = "HEAD";
    case ANY = "ANY";

    public function value(): string
    {
        return $this->value;
    }
}
