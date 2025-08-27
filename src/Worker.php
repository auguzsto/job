<?php
namespace Auguzsto\Job;

use ReflectionClass;
use ReflectionMethod;
use Auguzsto\Job\Runner;
use Auguzsto\Job\RunnerInterface;
use ReflectionObject;

class Worker
{
    public const DIR = __DIR__ . "/.queue";

    public static function listen(string $id): never
    {
        $fileQueue = self::DIR . "/$id";
        while (true) {
            $content = file_get_contents($fileQueue);
            $queue = json_decode($content);
            if (empty($queue->callable)) {
                sleep(1);
                continue;
            }

            [$class, $method, $args] = explode("::", $queue->callable);
            $classmethod = "$class::$method";
            $args = json_decode($args);
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
            
            $queue->callable = "";
            file_put_contents($fileQueue, json_encode($queue));
            sleep(1);
        }
    }

    public static function up(int $amount = 10, RunnerInterface $runner = new Runner()): array
    {
        $dirqueue = self::DIR;
        if (!is_dir($dirqueue)) {
            mkdir($dirqueue);
        }

        $bin = $runner->bin();
        $class = self::class;
        $method = "listen";
        $classmethod = escapeshellarg("$class::$method");
        $ups = [];

        for ($i = 1; $i <= $amount; $i++) {
            $fileQueue = "$dirqueue/$i";
            if (file_exists($fileQueue)) {
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
            file_put_contents($fileQueue, json_encode($content));
            array_push($ups, "Worker up: $i");
        }

        return $ups;
    }

    public static function down(): array
    {
        $dirqueue = self::DIR;
        if (!is_dir($dirqueue)) {
            mkdir($dirqueue);
        }
        
        $workers = array_diff(scandir($dirqueue), [".", ".."]);
        $downs = [];
        foreach ($workers as $key => $worker) {
            $content = json_decode(file_get_contents("$dirqueue/$worker"));
            $handle = popen("kill -9 {$content->pid}", "r");
            pclose($handle);
            unlink("$dirqueue/$worker");
            array_push($downs, "Worker down: $worker");
        }

        return $downs;
    }
}