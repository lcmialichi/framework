<?php

namespace App\Container;

use App\Helpers\ClassLoader;
use App\Resources\Providers\Provider;

class ContainerService
{
    private ClassLoader $loader;

    public function __construct(private ContainerInterface $container)
    {
        $this->loader = new ClassLoader(
            config('app.providers.namespace'),
            true
        );
    }

    public static function generate(ContainerInterface $container): void
    {
        (new self($container))->build();
    }

    public function build()
    {
        foreach ($this->loader->load() as $provider) {
            if (($instance = resolve($provider)) instanceof Provider) {
                if (method_exists($instance, "register")) {
                    $this->container->make(
                        $instance->{"register"}(...)
                    );
                }
            }
        }

    }
}
