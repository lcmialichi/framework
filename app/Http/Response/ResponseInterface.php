<?php

namespace App\Http\Response;

use App\FileSystem\StreamInterface;

interface ResponseInterface
{
    public function getStatusCode(): int;
    public function getHeaders(): array;
    public function getBody(): StreamInterface;
    public function addHeader(string $key, string $value): Static;
    public function removeHeader(string $key): void;
    public function setStatusCode(int $statusCode): static;
}
