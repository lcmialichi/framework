<?php

namespace App\Validation\Handlers;

use Validators\Contracts\ValidatorHandler;

class Email implements ValidatorHandler
{
    public function handle($value): bool
    {
        return true;
    }
}
