<?php
namespace Auguzsto\Job;
use Auguzsto\Job\Runner;
use Auguzsto\Job\Process;
use Auguzsto\Job\JobException;
use Auguzsto\Job\JobInterface;
use Auguzsto\Job\RunnerInterface;
use Auguzsto\Job\ProcessInterface;
use Auguzsto\Job\Exceptions\ClassNotExistsException;
use Auguzsto\Job\Exceptions\MethodNotExistsException;

class Job implements JobInterface
{
    private string $class;
    private string $method;
    private array $args;

    public function __construct(string $class = "", string $method = "", array $args = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->args = $args;
    }

    public function process(): ProcessInterface
    {
        return new Process();
    }
    public function runner(): RunnerInterface
    {
        return new Runner();
    }

    public function execute(): int
    {
        try {
            if (!$this->checkClassExists($this->class)) {
                throw new ClassNotExistsException("Class not found");
            }

            if (!$this->checkMethodExists($this->class, $this->method)) {
                throw new MethodNotExistsException("Method not found");
            }

            $bin = $this->runner()->bin();
            $classmethod = escapeshellarg("{$this->class}::{$this->method}");
            $args = escapeshellarg(json_encode($this->args));

            $cmd = "php $bin $classmethod $args > /dev/null 2>&1 & echo $!";
            exec($cmd, $output);

            $pid = $output[0];
            $this->process()->createFile($pid, $cmd);
            return $pid;
        } catch (JobException $th) {
            throw $th;
        }
    }

    private function checkMethodExists(string $class, string $method): bool
    {
        if (method_exists($class, $method)) {
            return true;
        }

        return false;
    }

    private function checkClassExists(string $class): bool
    {
        if (class_exists($class)) {
            return true;
        }

        return false;
    }
}