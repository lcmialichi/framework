<?php

namespace App\Resources\Providers;

use Validators\Validator;
use App\Validation\Message;

class ValidationHandlerProvider extends Provider
{
    public function register(): void
    {
        $this->app->bind(Validator::class, function () {
            $validator = new Validator();
            $validator->addNamespaceHandler("\\App\Validation\\Handlers");
            $validator->registerMessages(new Message(trans("messages.validation")));
            return $validator;
        });
    }
}
