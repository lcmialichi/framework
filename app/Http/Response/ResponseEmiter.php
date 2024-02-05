<?php

namespace App\Http\Response;

use App\Http\Response\ResponseInterface;

class ResponseEmiter
{
    public function __construct(private ResponseInterface $response)
    {
    }

    public function emit(): void
    {
        $this->emitHttpCode();
        $this->emitHeaders();
        $this->emitBody();
    }

    private function emitHeaders(): void
    {
        foreach ($this->response->getHeaders() as $key => $header) {
            header("{$key}: {$header}");
        }
    }

    private function emitBody(): void
    {
        $body = $this->response->getBody();
        $body->rewind();
        while (!$body->eof()) {
            $this->startBuffering();
            echo $body->read();
            $this->stopBuffering();
        }

        $body->close();
    }

    private function startBuffering(): void
    {
        ob_start();
    }

    private function stopBuffering(): void
    {
        ob_end_flush();
    }

    private function emitHttpCode(): void
    {
        http_response_code($this->response->getStatusCode());
    }

}
