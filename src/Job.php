<?php
namespace Auguzsto\Job;
use Auguzsto\Job\Exceptions\ClassNotExistsException;
use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\JobException;
use Auguzsto\Job\RunnerInterface;
use Auguzsto\Job\ProcessInterface;

class Job
{
    private string $class;
    private string $method;
    private array $args;
    public RunnerInterface $runner;
    public ProcessInterface $process;

    public function __construct(string $class = "", string $method = "", array $args = [])
    {
        $this->runner = new Runner();
        $this->process = new Process();
        $this->class = $class;
        $this->method = $method;
        $this->args = $args;
    }

    public function execute(): void
    {
        try {
            if (!$this->checkClassExists($this->class)) {
                throw new ClassNotExistsException("Class not found");
            }

            if (!$this->checkMethodExists($this->class, $this->method)) {
                throw new MethodNotExistsException("Method not found");
            }

            $bin = $this->runner->bin();
            $classmethod = escapeshellarg("{$this->class}::{$this->method}");
            $args = escapeshellarg(json_encode($this->args));

            $cmd = "php $bin $classmethod $args > /dev/null 2>&1 & echo $!";
            exec($cmd, $output);

            $pid = $output[0];
            $this->process->createFile($pid, $cmd);
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