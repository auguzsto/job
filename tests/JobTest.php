<?php

use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Time;
use Auguzsto\Job\Worker;
use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Tests\Backup;
use Auguzsto\Job\Tests\Request;
use PHPUnit\Framework\TestCase;
use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\Exceptions\NoActiveWorkersException;

final class JobTest extends TestCase
{
    public function testThrowExceptionWithoutActiveWorkers(): void
    {
        $this->expectException(NoActiveWorkersException::class);
        Worker::down();
        $job = new Job(Backup::class, "larger", [1]);
        $job->execute();
    }

    public function testUpWorkers(): void
    {
        $result = Worker::up();
        $this->assertIsArray($result);
        $this->assertEquals(10, count($result));
    }

    public function testJobPerformedConcorrency(): void
    {
        $jobs = new GroupJob([
            new Job(Backup::class, "larger", [1]),
            new Job(Backup::class, "larger", [5]),
            new Job(Backup::class, "larger", [8]),
        ]);
        $queues = $jobs->execute();
        $this->assertIsArray($queues);
        $this->assertEquals(3, count($queues));
        sleep(3);
        $result = file_exists("backup_large_1.txt");
        $this->assertTrue($result);

    }

    public function testAbortIfMethodNotExists(): void
    {
        $this->expectException(MethodNotExistsException::class);
        $this->expectExceptionMessage("Method not found");
        $job = new Job(Request::class, "methodNotExists");
        $job->execute();
    }

    public function testRunningJobWithArgs(): void
    {
        $job = new Job(Request::class, "slowBy", [35]);
        $this->assertIsInt($job->execute());
    }

    public function testJobRunningMethodInstance(): void
    {
        $job = new Job(Backup::class, "big", [new Time(2)]);
        $queue = $job->execute();
        $this->assertIsInt($queue);
    }
    
}