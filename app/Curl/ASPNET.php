<?php

namespace App\Curl;

abstract class ASPNET extends Curl
{
    private ?string $viewState = "";
    private ?string $viewStateGenerator = "";
    private ?string $eventValidation = "";
    private ?CurlResponse $lastResponse = null;
    private array $errorHandler = [];

    public function post(string $url, mixed $post, ?array $header = null): CurlResponse
    {
        if (is_array($post)) {
            $post["__VIEWSTATE"] = $post["__VIEWSTATE"] ?? $this->viewState();
            $post["__VIEWSTATEGENERATOR"] = $post["__VIEWSTATEGENERATOR"] ?? $this->viewStateGenerator();
            $post["__EVENTVALIDATION"] = $post["__EVENTVALIDATION"] ?? $this->eventValidation();
        }
        if ($header === null) {
            $header = $this->headers();
        }

        $request = parent::post($url, $post, $header);
        $this->setLastResponse($request);
        $this->refreashHeaders();

        if ($this->getOption('followLocation') == true) {
            if ($request->contains('pageRedirect||')) {
                return $this->redirect();
            }
        }
        return $request;
    }

    public function get(string $url, ?array $header = null): CurlResponse
    {
        if ($header === null) {
            $header = $this->headers();
        }
        $request = parent::get($url, $header);
        $this->setLastResponse($request);
        $this->refreashHeaders();

        if ($this->getOption('followLocation') === true) {
            if ($request->contains('pageRedirect||')) {
                return $this->redirect();
            }
        }

        return $request;
    }

    private function redirect(): CurlResponse
    {
        $url = urldecode($this->lastResponse()->explode('pageRedirect||', '|'));
        if (beginsWith($this->domain(), $url)) {
            $url = substr($url, strlen($this->domain()));
        }   

        if(empty($url)){
            throw new \Exception("Invalid redirect url");
        }

        return $this->get($url);
    }

    protected function lastResponse(): ?CurlResponse
    {
        return $this->lastResponse;
    }

    private function setLastResponse(CurlResponse $response): void
    {
        $this->lastResponse = $response;
        $this->setViewState();
        $this->setViewStateGenerator();
        $this->setEventValidation();
    }

    private function setViewState(): void
    {
        $this->viewState = $this->extract("__VIEWSTATE");
    }

    private function setViewStateGenerator(): void
    {
        $this->viewStateGenerator = $this->extract("__VIEWSTATEGENERATOR");
    }

    private function setEventValidation(): void
    {
        $this->eventValidation = $this->extract("__EVENTVALIDATION");
    }

    private function extract(string $value): string
    {
        $id = sprintf('id="%s" value="', $value);
        if ($this->lastResponse()->contains($id)) {
            return $this->lastResponse()->explode($id, '"');
        }
        
        $pipe = sprintf('|%s|', $value);
        if ($this->lastResponse()->contains($pipe)) {
            return $this->lastResponse()->explode($pipe, '|');
        }

        return "";
    }

    public function viewState(): ?string
    {
        return $this->viewState;
    }

    public function viewStateGenerator(): ?string
    {
        return $this->viewStateGenerator;
    }

    public function eventValidation(): ?string
    {
        return $this->eventValidation;
    }

    /** @abstract */
    protected abstract function headers(): array;

    protected abstract function refreashHeaders(): void;

    protected function addErrorHandler(callable $callabe): void
    {
        $this->errorHandler[] = $callabe;
    }

    public function errors(): mixed
    {
        foreach ($this->errorHandler as $errorFinder) {
            if ($error = $errorFinder($this->lastResponse())) {
                return $error;
            }
        }

        return false;
    }
}
