<?php

namespace App\FileSystem;

use App\FileSystem\StreamInterface;

class Stream implements StreamInterface
{
    /** @var resource */
    private $stream;

    /** @var string */
    private string $path;

    private const FILE_CHUNK_SIZE = 1024;

    public function __construct(?string $path = null)
    {
        $this->buildStream($path);
    }

    public function buildStream(?string $path)
    {
        if ($path == null) {
            $path = $this->makeTmp();
        }
        $this->path = $path;
        $this->stream = fopen($path, "w+");
    }

    private function makeTmp(): string
    {
        return tempnam(sys_get_temp_dir(), "stream_" . time());
    }

    public function write(string $content): void
    {
        if($this->isWritable() === false) {
            throw new \RuntimeException("Stream is not writable");
        }

        fwrite($this->stream, $content);
        $this->flush();
    }

    public function read(?int $chunk = null): string
    {
        if ($this->isReadable() === false) {
            throw new \RuntimeException("Stream is not readable");
        }
    
        $chunk = $chunk ?? self::FILE_CHUNK_SIZE;

        $result = fread($this->stream, $chunk);
        if (!$result) {
            throw new \RuntimeException("Error reading the stream");
        }

        return $result;
    }

    public function getContents(): string
    {
        if ($this->isReadable() === false) {
            throw new \RuntimeException("Stream is not readable");
        }
        $this->rewind();
        $result = stream_get_contents($this->stream);
        if (!$result) {
            throw new \RuntimeException("Error reading the stream");
        }

        return $result;
    }

    public function eof(): bool
    {
        return feof($this->stream);
    }

    public function isReadable(): bool
    {
        return is_readable($this->getPath());
    }

    public function isWritable(): bool
    {
        return is_writable($this->getPath());
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getSize(): int
    {
        return filesize($this->getPath());
    }

    public function close(): void
    {
        if (isset($this->stream)) {
            fclose($this->stream);
        }
    }

    public function rewind(): void
    {
        rewind($this->stream);
    }

    public function flush(): void
    {
        fflush($this->stream);
    }

    public function chmod(int $mode): void
    {
        chmod($this->getPath(), $mode);
    }
}
