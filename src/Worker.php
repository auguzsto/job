<?php
namespace Auguzsto\Job;

use Auguzsto\Job\RunnerInterface;

class Worker
{
    private const DIR = __DIR__ . "/.queue";

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
            call_user_func_array($classmethod, $args);
            $queue->callable = "";
            file_put_contents($fileQueue, json_encode($queue));
            sleep(1);
        }
    }

    public static function up(int $amount = 2, RunnerInterface $runner = new Runner()): void
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
            if (file_exists($fileQueue))
                continue;

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

        fwrite(STDOUT, json_encode($ups) . PHP_EOL);
    }

    public static function down(): void
    {
        $dirqueue = self::DIR;
        $workers = array_diff(scandir($dirqueue), [".", ".."]);
        $downs = [];
        foreach ($workers as $key => $worker) {
            $content = json_decode(file_get_contents("$dirqueue/$worker"));
            $handle = popen("kill -9 {$content->pid}", "r");
            pclose($handle);
            unlink("$dirqueue/$worker");
            array_push($downs, "Worker down: $worker");
        }

        fwrite(STDOUT, json_encode($downs) . PHP_EOL);
    }
}