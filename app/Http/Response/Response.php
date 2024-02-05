<?php

namespace App\Http\Response;

use App\Http\Response\ResponseInterface;
use App\Http\Response\Handlers\JsonResponse;

class Response
{
    public static function json(array $body, int $code = 200, array $headers = []): ResponseInterface
    {
        return new JsonResponse($body, $code, $headers);
    }
}
