<?php
namespace Auguzsto\Job;
use Auguzsto\Job\Exceptions\NoActiveWorkersException;
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
            if (!$this->checkWorkersEnables()) {
                throw new NoActiveWorkersException("No active workers. Try restarting.");
            }

            if (!$this->checkClassExists($this->class)) {
                throw new ClassNotExistsException("Class not found");
            }

            if (!$this->checkMethodExists($this->class, $this->method)) {
                throw new MethodNotExistsException("Method not found");
            }
            $dirworkers = Worker::DIR;

            $workers = array_diff(scandir($dirworkers), [".", ".."]);
            $randomId = random_int(1, count($workers));
            $fileWorker = "$dirworkers/$randomId";
            $content = file_get_contents($fileWorker);
            $worker = unserialize($content);
            
            if (empty($worker["callable"])) {
                $worker["callable"] = [$this->class, $this->method, $this->args];
                file_put_contents($fileWorker, serialize($worker));
                return $randomId;
            }

            sleep(1);
            return $this->execute();
        } catch (JobException $th) {
            throw $th;
        }
    }

    private function checkWorkersEnables(): bool
    {
        $dir = Worker::DIR;
        $workers = array_diff(scandir($dir), [".", ".."]);
        if (count($workers) > 0) {
            return true;
        }
        
        return false;
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