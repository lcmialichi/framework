<?php

namespace App\Exception;

use App\Console\Command;
use App\Http\Response\ResponseInterface;
use App\Contracts\ExceptionHandlerInterface;

class CommandHandler extends Command implements ExceptionHandlerInterface
{
    public function __construct()
    {
    }

    public function render(\Throwable $error):  null|ResponseInterface
    {
        $this->error($error->getMessage());
        return null;
    }

}
