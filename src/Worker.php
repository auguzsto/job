<?php
namespace Auguzsto\Job;

use ReflectionClass;
use ReflectionMethod;
use Auguzsto\Job\Runner;
use Auguzsto\Job\RunnerInterface;

class Worker
{
    public const DIR = __DIR__ . "/.workers";

    public static function listen(string $id): never
    {
        $fileWorker = self::DIR . "/$id";
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
                //call_user_func_array($classmethod, $args);
                if (empty($args)) {
                    $methodReflection->invoke(null);
                }

                if (!empty($args)) {
                    $methodReflection->invokeArgs(null, $args);
                }
            }

            if (!$methodReflection->isStatic()) {
                // $instance = new $class();
                // call_user_func_array([$instance, $method], $args);
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
    }

    public static function up(int $amount = 10, RunnerInterface $runner = new Runner()): array
    {
        $dirworker = self::DIR;
        if (!is_dir($dirworker)) {
            mkdir($dirworker);
        }

        $bin = $runner->bin();
        $class = self::class;
        $method = "listen";
        $classmethod = escapeshellarg("$class::$method");
        $ups = [];

        for ($i = 1; $i <= $amount; $i++) {
            $fileWorker = "$dirworker/$i";
            if (file_exists($fileWorker)) {
                array_push($ups, "Worker up: $i");
                continue;
            }

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
            array_push($ups, "Worker up: $i");
        }

        return $ups;
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