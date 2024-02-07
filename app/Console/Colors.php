<?php

namespace App\Console;

class Colors
{
    public function __construct(private string $hexadecimal)
    {
    }

    public static function fromHexadecimal(string $hexa): self
    {
        return new self($hexa);
    }

    public function toRGB(): string
    {
        list($r, $g, $b) = sscanf($this->hexadecimal, "#%02x%02x%02x");
        return "$r;$g;$b";
    }
}

