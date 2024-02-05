<?php

namespace App\Exception;

use App\Http\Response\ResponseInterface;
use App\Contracts\ExceptionHandlerInterface;
use App\Http\Response\Handlers\JsonResponse;
use App\Enum\Http;


class Handler implements ExceptionHandlerInterface
{
    public function render(\Throwable $error): null|ResponseInterface
    {
        return $this->getResponseToSend(
            $error->getMessage(),
            $this->resolveHttpCode($error)
        );
    }

    private function resolveHttpCode(\Throwable $error): int
    {
        return Http::tryFrom($error->getCode())?->value ?? Http::BAD_REQUEST->value;
    }

    private function getResponseToSend(
        string $body,
        int $httpCode,
        array $headers = []
    ): ResponseInterface {
        $message = [
            "status" => false,
            "code" => $httpCode,
            "message" => $body,
        ];
        return new JsonResponse(
            $message,
            $httpCode,
            $headers
        );
    }
}
