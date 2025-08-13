<?php
namespace Auguzsto\Job;
use Auguzsto\Job\Exceptions\ClassNotExistsException;
use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\JobException;
use Auguzsto\Job\RunnerInterface;
use Auguzsto\Job\ProcessInterface;

class Job
{
    public RunnerInterface $runner;
    public ProcessInterface $process;

    public function __construct()
    {
        $this->runner = new Runner();
        $this->process = new Process();
    }

    public function execute(string $class, string $method, array $args = []): void
    {
        try {
            if (!$this->checkClassExists($class)) {
                throw new ClassNotExistsException("Class not found");
            }

            if (!$this->checkMethodExists($class, $method)) {
                throw new MethodNotExistsException("Method not found");
            }

            $runner = $this->runner->bin();
            $classmethod = escapeshellarg("$class::$method");
            $args = escapeshellarg(json_encode($args));

            $cmd = "php $runner $classmethod $args > /dev/null 2>&1 & echo $!";
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