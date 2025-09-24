<?php
namespace Auguzsto\Job;

use Auguzsto\Job\Exceptions\WorkerIdExceedsMaximumLimitException;
use Auguzsto\Job\Exceptions\WorkerNotAvailableException;
use InvalidArgumentException;
use ReflectionMethod;

class Worker
{
    public const DIR = __DIR__ . "/.workers";
    private const BIN = __DIR__ . "/../bin/job";
    private const MAX_WORKERS = 10;

    public static function listen(int $id): never
    {
        $fileWorker = self::DIR . "/$id";
        try {
            while (true) {
                if (!file_exists($fileWorker)) {
                    sleep(1);
                    continue;
                }

                $content = file_get_contents($fileWorker);
                $worker = unserialize($content);
                if (empty($worker["callable"])) {
                    sleep(1);
                    continue;
                }

                [$class, $method, $args] = $worker["callable"];
                $methodReflection = new ReflectionMethod($class, $method);

                if ($methodReflection->isStatic()) {
                    $instance = new $class();
                    if (empty($args)) {
                        $methodReflection->invoke(null);
                    }

                    if (!empty($args)) {
                        $methodReflection->invokeArgs(null, $args);
                    }
                }

                if (!$methodReflection->isStatic()) {
                    $instance = new $class();
                    if (empty($args)) {
                        $methodReflection->invoke($instance);
                    }

                    if (!empty($args)) {
                        $methodReflection->invokeArgs($instance, $args);
                    }
                }

                $worker["callable"] = "";
                file_put_contents($fileWorker, serialize($worker));
                sleep(1);
            }
        } catch (\Throwable $th) {
            unlink($fileWorker);
            self::up($id);
            throw $th;
        }
    }

    /**
     * Is a worker auto-up.
     * @param int $max
     * @return int
     */
    public static function register(): int
    {
        $dirworker = self::DIR;
        if (!is_dir($dirworker)) {
            mkdir($dirworker);
        }

        $i = count(self::workers());
        $fileWorker = "$dirworker/$i";

        if ($i > self::MAX_WORKERS) {
            return random_int(0, self::MAX_WORKERS);
        }

        if ($i > 0) {
            $last = random_int(0, $i - 1);
            $fileWorkerLast = "$dirworker/$last";
            if (file_exists($fileWorkerLast)) {
                $content = file_get_contents($fileWorkerLast);
                $workerLast = unserialize($content);
                if (empty($workerLast["callable"])) {
                    return $last;
                }
            }
        }

        $bin = self::BIN;
        $class = self::class;
        $method = "listen";
        $classmethod = escapeshellarg("$class::$method");

        $args = escapeshellarg($i);
        $cmd = "php $bin $classmethod [$args] > /dev/null 2>&1 & echo $!";
        $handle = popen($cmd, "r");
        $buffer = fread($handle, 2096);
        $pid = $buffer;
        pclose($handle);

        $content = [
            "pid" => trim($pid),
            "callable" => "",
        ];
        file_put_contents($fileWorker, serialize($content));
        return $i;
    }

    /**
     * Manually create a worker if one does not exist.
     * @param int $id
     * @return int
     */
    public static function up(int $id): void
    {
        $dirworker = self::DIR;
        $fileWorker = "$dirworker/$id";
        
        if ($id > self::MAX_WORKERS) {
            throw new WorkerIdExceedsMaximumLimitException();
        }

        if (file_exists($fileWorker)) {
            throw new WorkerNotAvailableException();
        }

        $bin = self::BIN;
        $class = self::class;
        $method = "listen";
        $classmethod = escapeshellarg("$class::$method");

        $args = escapeshellarg($id);
        $cmd = "php $bin $classmethod [$args] > /dev/null 2>&1 & echo $!";
        $handle = popen($cmd, "r");
        $buffer = fread($handle, 2096);
        $pid = $buffer;
        pclose($handle);

        $content = [
            "pid" => trim($pid),
            "callable" => "",
        ];
        file_put_contents($fileWorker, serialize($content));
    }

    public static function workers(): array
    {
        $workers = array_diff(scandir(self::DIR), [".", ".."]);
        return $workers;
    }

    public static function down(): array
    {
        $dirworker = self::DIR;
        if (!is_dir($dirworker)) {
            mkdir($dirworker);
        }

        $workers = array_diff(scandir($dirworker), [".", ".."]);
        $downs = [];
        foreach ($workers as $key => $worker) {
            $content = unserialize(file_get_contents("$dirworker/$worker"));
            $pid = $content["pid"];
            $handle = popen("kill -9 $pid", "r");
            pclose($handle);
            unlink("$dirworker/$worker");
            array_push($downs, "Worker down: $worker");
        }

        return $downs;
    }
}