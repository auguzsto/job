<?php

use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Backup;
use Auguzsto\Job\Tests\Request;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{

    public function testUpWorkers(): void
    {
        $init = 2;
        $binWorker = __DIR__ . "/../src/worker";
        $handle = popen("php $binWorker up $init", "r");
        $buffer = fread($handle, 2096);
        $array = json_decode($buffer);
        $this->assertIsArray($array);
        $this->assertEquals($init, count($array));
    }

    public function testJobPerformedConcorrency(): void
    {
        $jobs = new GroupJob([
            new Job(Backup::class, "larger", [15]),
            new Job(Backup::class, "larger", [30]),
            new Job(Backup::class, "larger", [45]),
        ]);
        $queues = $jobs->execute();
        $this->assertIsArray($queues);
        $this->assertEquals(3, count($queues));
    }
}