<?php



namespace App\Event\Connection\Adapter;

use App\Configuration\Environment;
use App\Event\Connection\Connection;

class AMQPConnection implements Connection
{
    public function user(): string
    {
        return $this->env()->get('RABBITMQ_USER');
    }

    public function password(): string
    {
        return $this->env()->get('RABBITMQ_PASSWORD');
    }

    public function host(): string
    {
        return $this->env()->get('RABBITMQ_HOST');
    }

    public function port(): int
    {
        return (int) $this->env()->get('RABBITMQ_PORT');
    }

    private function env(): Environment
    {
        return Environment::make();
    }
}
