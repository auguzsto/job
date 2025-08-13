<?php
namespace Auguzsto\Job;

use stdClass;
use Auguzsto\Job\ProcessInterface;

class Process implements ProcessInterface
{
    public int $pid;
    private string $dir = self::DIR;

    public function setPid(int $pid): void
    {
        $this->pid = $pid;
    }

    public function getPid(): int
    {
        return $this->pid;
    }

    public function isRunning(): bool
    {
        if (is_file($this->dir . "/{$this->getPid()}")) {
            return true;
        }

        return false;
    }

    public function createFile(int $pid, string $content): void
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }

        $this->setPid($pid);
        file_put_contents("{$this->dir}/{$this->pid}", $content);
    }

    public function running(): array
    {
        $result = [];
        $pids = scandir($this->dir);
        foreach ($pids as $key => $pid) {
            if (is_dir($pid))
                continue;

            $running = file_get_contents("{$this->dir}/$pid");
            if (empty($running))
                continue;

            $process = new stdClass();
            $process->pid = $pid;
            $process->running = $running;
            array_push($result, $process);
        }
        return $result;
    }
}