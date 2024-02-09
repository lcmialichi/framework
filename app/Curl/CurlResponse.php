<?php

namespace App\Curl;

final class CurlResponse
{
    public function __construct(
        private array $headers = [],
        private array $curlInfo = [],
        private string $response = ""
    ) {
    }

    public function object(): ?object
    {
        return json_decode($this->response);
    }

    public function json($key = null): mixed
    {
        $data = json_decode($this->body(), true);
        if ($key) {
            return dot($key, $data);
        }
        return $data;
    }

    public function statusCode()
    {
        return $this->curlInfo['http_code'];
    }

    public function success(): bool
    {
        return $this->statusCode() >= 200 && $this->statusCode() < 300;
    }

    public function notFound(): bool
    {
        return $this->statusCode() == 404;
    }

    public function unauthorized(): bool
    {
        return $this->statusCode() == 401;
    }

    public function badRequest(): bool
    {
        return $this->statusCode() == 400;
    }

    public function internalServerError(): bool
    {
        return $this->statusCode() == 500;
    }

    public function redirect(): bool
    {
        return $this->statusCode() == 302;
    }

    public function body(): string
    {
        return $this->response;
    }

    public function getHeaders(?string $header = null): null|array|string
    {
        if ($header) {
            return $this->headers[$header] ?? null;
        }
        return $this->headers;
    }

    public function getInfo(?string $info = null): array|string
    {
        if ($info) {
            return $this->curlInfo[$info];
        }
        return $this->curlInfo;
    }

    public function contains(string $string): bool
    {
        return str_contains($this->response, $string);
    }

    public function getStatusCode()
    {
        return $this->getInfo('http_code');
    }

    public function explode(...$values)
    {
        $body = $this->body();
        foreach ($values as $index => $value) {
            if (is_array($value)) {
                $body = explode($value[0], $body)[$value[1]];
                continue;
            }

            $body = explode($value, $body)[!($index % 2)] ?? null;
            if (!$body) {
                return null;
            }
        }

        return $body;
    }

    public function getHtmlValue(string $name, string $type = 'input'): string
    {
        if ($type == 'input') {
            return $this->explode("name=\"{$name}\" value=\"", '"') ?? "";
        }

        $options = $this->explode("name=\"{$name}\"", "</select>");
        foreach (explode('<option', $options) as $option) {
            if (str_contains($option, 'selected')) {
                return explode("\"", explode("value=\"", $option)[1])[0] ?? "";
            }
        }

        return "";

    }
}
