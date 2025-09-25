<?php
namespace Auguzsto\Job;
use Auguzsto\Job\JobException;
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

    public function execute(): int
    {
        try {
            if (!class_exists($this->class)) {
                throw new ClassNotExistsException("Class not found");
            }

            if (!method_exists($this->class, $this->method)) {
                throw new MethodNotExistsException("Method not found");
            }
            
            $dirworkers = Worker::DIR;
            $workerId = Worker::register();
            $fileWorker = "$dirworkers/$workerId";
            $content = file_get_contents($fileWorker);
            $worker = unserialize($content);

            if (empty($worker["callable"])) {
                $worker["callable"] = [$this->class, $this->method, $this->args];
                file_put_contents($fileWorker, serialize($worker));
                return $workerId;
            }

            sleep(1);
            return $this->execute();
        } catch (JobException $th) {
            throw $th;
        }
    }
}