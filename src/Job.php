<?php
namespace Auguzsto\Job;
use Auguzsto\Job\Exceptions\IncludeNotExistsException;
use Auguzsto\Job\JobException;
use Auguzsto\Job\Exceptions\ClassNotExistsException;
use Auguzsto\Job\Exceptions\MethodNotExistsException;

class Job implements JobInterface
{
    private string $class;
    private string $method;
    private array $args;
    private string $include = "";

    public function __construct(string $class = "", string $method = "", array $args = [])
    {
        $this->class = $class;
        $this->method = $method;
        $this->args = $args;
    }

    public function include(string $path): void
    {
        $this->include = $path;
    }

    public function execute(): int
    {
        try {
            if (!class_exists($this->class) && empty($this->include)) {
                throw new ClassNotExistsException("Class not found");
            }

            if (!method_exists($this->class, $this->method) && empty($this->include)) {
                throw new MethodNotExistsException("Method not found");
            }
            
            if (!file_exists($this->include) && !empty($this->include)) {
                throw new IncludeNotExistsException("Include {$this->include} not found");
            }
            
            $dirworkers = Worker::DIR;
            $workerId = Worker::register();
            $fileWorker = "$dirworkers/$workerId";
            $content = file_get_contents($fileWorker);
            $worker = unserialize($content);

            if (empty($worker["callable"])) {
                $worker["callable"] = [$this->class, $this->method, $this->args, $this->include];
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