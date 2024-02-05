<?php

namespace App\Exception;

use App\Console\Displayer;
use App\Http\Response\ResponseInterface;
use App\Contracts\ExceptionHandlerInterface;

class CommandHandler implements ExceptionHandlerInterface
{
    public function __construct(private Displayer $console)
    {
    }

    public function render(\Throwable $error):  null|ResponseInterface
    {
        $this->console->error($error->getMessage());
        return null;
    }

}
