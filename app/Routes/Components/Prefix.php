<?php



namespace App\Routes\Components;

trait Prefix
{
    private string $prefix = "";

    public function prefix(string $prefix): self
    {
        $this->prefix = "/" . trim($prefix, "/");
        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

}
