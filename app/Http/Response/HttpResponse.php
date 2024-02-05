<?php

namespace App\Http\Response;

use App\FileSystem\Stream;
use App\FileSystem\StreamInterface;
use App\Http\Response\ResponseInterface;

abstract class HttpResponse implements ResponseInterface
{
    protected int $statusCode;
    protected array $headers;
    protected StreamInterface $body;

    public function __construct(
        string|StreamInterface $body = "",
        int $statusCode = 200,
        array $headers = []
    ) {
        $this->statusCode = $statusCode;
        if (!$body instanceof StreamInterface) {
            $body = $this->createStreamFromString($body);
        }
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function addHeader(string $key, string $value): static
    {
        $this->headers[$key] = $value;
        return $this;
    }

    private function createStreamFromString(string $body): StreamInterface
    {
        $stream = new Stream();
        $stream->write($body);
        return $stream;
    }

    public function removeHeader(string $key): void
    {
        unset($this->headers[$key]);
    }
}
