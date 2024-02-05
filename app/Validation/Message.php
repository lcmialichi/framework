<?php

namespace App\Validation;

class Message implements \Validators\Contracts\MessagesRegistration
{
    public function __construct(private array $messages = [])
    {

    }

    public function register(): array
    {
        return $this->messages;
    }
}
