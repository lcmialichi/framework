<?php

namespace App\Http\Request;

use App\Routes\Dispatcher;
use App\Container\ContainerService;
use App\Container\ContainerInterface;
use App\Http\Response\ResponseEmiter;
use App\Http\Response\ResponseInterface;
use App\Contracts\ExceptionHandlerInterface;

class RequestHandler
{
    public function __construct(
        private ContainerInterface $container,
        private Dispatcher $dispatcher,
        private ExceptionHandlerInterface $exceptionHandler
    ) {
    }

    public function execute(?string $name = null): void
    {
        $this->runProviders();
        $this->loadRoutes();
        $response = $this->prepareResponse($this->handle($name));
        if ($response) {
            $this->emit($response);
        }
    }

    private function handle(?string $name = null): mixed
    {
        try {
            if ($name !== null) {
                $response = $this->redirect($name);
            }
            
            $response = $response ?? $this->dispatcher->dispatch();
            if ($this->dispatcher->failed()) {
                $code = $this->dispatcher->getHttpCode()->value;
                throw new \Exception("Route not found", $code);
            }

            return $response;
        } catch (\Throwable $exception) {
            return $this->exceptionHandler->render($exception);

        }
    }

    public function redirect(string $name): mixed
    {
        return $this->dispatcher->redirect($name);
    }

    private function loadRoutes(): void
    {
        foreach ($this->getRoutesRegistered() as $route) {
            require $route;
        }
    }

    private function prepareResponse(mixed $response): false|ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }

        return false;
    }

    private function getRoutesRegistered(): array
    {
        return config("routes.register") ?? [];
    }

    private function emit(ResponseInterface $response): void
    {
        (new ResponseEmiter($response))->emit();
    }

    private function runProviders(): void
    {
        $this->container->make(ContainerService::class)->build();
    }

}
