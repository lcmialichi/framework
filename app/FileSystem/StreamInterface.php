<?php

namespace App\FileSystem;

interface StreamInterface
{
    public function write(string $content): void;
    public function read(?int $chunk = null): string;
    public function getContents(): string;
    public function eof(): bool;
    public function isReadable(): bool;
    public function isWritable(): bool;
    public function getPath(): string;
    public function getSize(): int;
    public function close(): void;
    public function rewind(): void;
    public function chmod(int $mode): void;
}
