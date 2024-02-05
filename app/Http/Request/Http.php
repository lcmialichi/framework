<?php

namespace App\Http\Request;

use App\Http\Request;
use App\Validators\Errors;
use App\Exception\ValidationException;

/** @deprecated version  this class has been deprecated */
abstract class Http extends Request
{
    /**
     * Define se sera lançado uma exceçao de validação
     *
     * @var boolean
     */
    protected bool $throwValidationError = true;

    private ?Errors $error = null;

    public function __construct()
    {
        parent::__construct(
            query: $_GET,
            request: $_POST,
            attributes: [],
            cookies: $_COOKIE,
            files: $_FILES,
            server: $_SERVER,
            content: file_get_contents("php://input")
        );
    }


    /**
     * Regras de validaçao nos campos do request
     */
    public abstract function rules(): array;
    /**
     * Validaçao diversas e permissao para receber o request
     */
    public abstract function validation();

    public function fireValidationWithRules()
    {
        if (method_exists($this, "rules")) {
            $errors = $this->validate($this->rules());
            if ($errors->fails()) {
                $this->error = $errors;
            }
        }
    }

    public function errors()
    {
        return $this->error;
    }

    /** @deprecated version */
    public function fireValidation()
    {
        if (method_exists($this, "validation")) {
            if ($this->validation() === false) {
                $this->route()->redirect("/erro/403");
            }
            ;
        }

        if (!is_null($this->error) && $this->thowValidationError()) {
            $this->throwOnFirstError($this->error);
        }
    }

    private function throwOnFirstError(Errors $exception)
    {
        throw new ValidationException($exception);
    }

    protected function thowValidationError(): bool
    {
        return $this->throwValidationError;
    }

    protected function setThowValidationError(bool $bool): void
    {
        $this->throwValidationError = $bool;
    }
}
