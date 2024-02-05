<?php

namespace App\Async;

class Async
{
    private array $pids = [];

    private array $thenHandler = [];


    public function add(callable $handler): self
    {
        $pipe = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new \Exception("Could not fork process");
        }
        if ($pid) {
            $this->pids[$pid] = $pipe[1];
            fclose($pipe[0]);
            return $this;
        }

        fwrite($pipe[0], serialize(resolve($handler(...))));
        fclose($pipe[0]);
        exit;
    }

    public function then(callable $handler): self
    {
        $lastPid = array_key_last($this->pids);
        if (!$lastPid) {
            throw new \Exception("No pid found");
        }

        $this->thenHandler[$lastPid] = $handler;
        return $this;
    }

    public function dispatch()
    {
        $results = [];
        foreach ($this->pids as $pid => $pipe) {
            pcntl_waitpid($pid, $status);
            $results[$pid] = @unserialize(stream_get_contents($pipe));
            if (isset($this->thenHandler[$pid])) {
                $results[$pid] = $this->thenHandler[$pid]($results[$pid]);
            }
            fclose($pipe);
        }

        $this->reset();
        return $results;
    }

    public function reset(): void
    {
        $this->pids = [];
        $this->thenHandler = [];
    }


    public static function make(): self
    {
        return new self;
    }
}
