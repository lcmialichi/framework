<?php

namespace App\Controller;

use App\Http\Response\Response;
use App\Http\Request\RequestInterface;

class ThisIsATestController
{
  public function index(RequestInterface $request)
  {
    $validator = validator([
      'name' => 'required',
      'email' => 'required|string|email',
      'password' => 'required|minLength:6'
    ]);

    $errors = $validator->validate($request->all());

    return Response::json([
      'message' => 'tudo certo!',
      'data' => [
        'request' => $request->all(),
        'validation' => [
          "success" => $errors->succeded(),
          "failed" => $errors->failed(),
          "errorCount" => $errors->countFails(),
          "errors" => $errors->getErrorsMessages()
        ]
      ]
    ]);
  }
}