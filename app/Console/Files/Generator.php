<?php

namespace App\Console\Files;

use App\Console\Command;
use App\FileSystem\Stream;

abstract class Generator extends Command
{
    private ?Stream $stream = null;

    protected abstract function name(): string;

    protected abstract function stub(): string;

    protected abstract function namespace (): string;

    protected abstract function folder(): string;

    public function handler()
    {
        if ($this->fileExists()) {
            $this->error("File {$this->name()} already exists!");
            return;
        }

        if (!is_dir($this->folder())) {
            mkdir($this->folder(), $this->fileMode(), true);
        }

        $this->stream()->write($this->proccessStub());
        $this->stream()->close();
        $this->stream()->chmod($this->fileMode());
        $this->finish();
    }

    protected function fileMode(): int
    {
        return 0777;
    }

    protected function proccessStub(): string
    {
        return $this->replace([
            "namespace" => $this->namespace(),
            "class" => pascalCase($this->name()),
        ]);
    }

    private function stream(): Stream
    {
        if ($this->stream === null) {
            $this->stream = new Stream($this->getPath());
        }
        return $this->stream;
    }

    private function fileExists(): bool
    {
        return file_exists($this->getPath());
    }

    private function getPath(): string
    {
        return $this->folder() . "/" . pascalCase($this->name()) . ".php";
    }

    protected function replace(array $replaces): string
    {
        $stub = $this->stub();
        foreach ($replaces as $key => $value) {
            $name = sprintf("{{%s}}", $key);
            $stub = str_replace($name, $value, $stub);
        }

        return $stub;
    }

    private function finish(): void
    {
        $this->output("Namespace", "#3f8a37", "", false);
        $this->output(sprintf(" '%s' ", "{$this->namespace()}\\{$this->name()}"), "#884c11", "", false);
        $this->output("generated with success", "#3f8a37");
    }

}
