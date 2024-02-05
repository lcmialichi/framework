<?php

namespace App\Contracts;

use App\Http\Response\ResponseInterface;

interface ExceptionHandlerInterface
{
    public function render(\Throwable $e): null|ResponseInterface;
}