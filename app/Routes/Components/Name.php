<?php



namespace App\Routes\Components;

trait Name
{
    private string $name = "";

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
}
