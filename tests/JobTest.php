<?php

use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Time;
use Auguzsto\Job\Worker;
use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Tests\Backup;
use Auguzsto\Job\Tests\Request;
use PHPUnit\Framework\TestCase;
use Auguzsto\Job\Exceptions\MethodNotExistsException;

final class JobTest extends TestCase
{

    public function testUpWorkers(): void
    {
        $result = Worker::up();
        $this->assertIsArray($result);
        $this->assertEquals(10, count($result));
        Worker::down();
    }

    public function testRegisterWorker(): void
    {
        $job = new Job(Request::class, "slowBy", [1]);
        $worker = $job->execute();
        $this->assertIsInt($worker);
    }

    public function testRegisterWorkerAtMaximumCapacity(): void
    {   
        $registered = 0;
        while ($registered < 10) {
            $job = new Job(Request::class, "slowBy", [1]);
            $worker = $job->execute();
            $registered = $worker;
        }

        $this->assertEquals(10, $registered);
    }

    public function testJobPerformedConcorrency(): void
    {
        $jobs = new GroupJob([
            new Job(Backup::class, "larger", [1]),
            new Job(Backup::class, "larger", [5]),
            new Job(Backup::class, "larger", [8]),
        ]);
        $workers = $jobs->execute();
        $this->assertIsArray($workers);
        $this->assertEquals(3, count($workers));
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
        $job = new Job(Request::class, "slowBy", [5]);
        $this->assertIsInt($job->execute());
    }

    public function testJobRunningMethodInstance(): void
    {
        $job = new Job(Backup::class, "big", [new Time(2)]);
        $worker = $job->execute();
        $this->assertIsInt($worker);
    }
    
}