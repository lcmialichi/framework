<?php

namespace App\Curl;

class CurlHandle
{
    private \CurlHandle|bool $curl;

    private string|bool $response;

    private mixed $info;

    private string $error;

    private int $errorNumber;

    private ?string $errorDescription;


    public function __construct(array $options = [])
    {
        $this->startCurlSession($options);
    }

    private function startCurlSession(array $options): void
    {
        $this->curl = $this->initCurl($options);
    }

    private function initCurl(array $options)
    {
        $curl = curl_init();
        curl_setopt_array($curl, $options);
        return $curl;
    }

    public function getResponse(): string|bool
    {
        return $this->response;
    }

    public function getInfo(): array
    {
        return $this->info;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function getErrorNumber(): int
    {
        return $this->errorNumber;
    }

    public function getErrorDescription(): string
    {
        return $this->errorDescription;
    }

    public function execute(): void
    {
        $this->response = curl_exec($this->curl);
        $this->info = curl_getinfo($this->curl);
        $this->error = curl_error($this->curl);
        $this->errorNumber = curl_errno($this->curl);
        $this->errorDescription = curl_strerror($this->errorNumber);
        $this->close();
    }

    private function close(): void
    {
        curl_close($this->curl);
    }
}
