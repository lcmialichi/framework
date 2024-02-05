<?php

namespace App\Http\Response\Handlers;

use  App\Http\Response\HttpResponse;

class JsonResponse extends HttpResponse
{
    public function __construct(
        array $body = [],
        int $statusCode = 200,
        array $headers = []
    ) {
        parent::__construct(
            json_encode(
                $body,
                JSON_PRETTY_PRINT |
                JSON_UNESCAPED_SLASHES
            ),
            $statusCode,
            $headers
        );

        $this->addHeader("Content-Type", "application/json");
    }
}
