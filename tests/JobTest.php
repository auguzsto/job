<?php

use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Mocks\Time;
use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Tests\Mocks\Backup;
use Auguzsto\Job\Tests\Mocks\Request;
use Auguzsto\Job\Worker;
use PHPUnit\Framework\TestCase;
use Auguzsto\Job\Exceptions\MethodNotExistsException;

final class JobTest extends TestCase
{
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

    public function testJobRunningArgsObjectInstance(): void
    {
        $job = new Job(Backup::class, "big", [new Time(2)]);
        $worker = $job->execute();
        $this->assertIsInt($worker);
    }
    public function testReturnArrayWithWorkersUps(): void
    {
        $result = Worker::workers();
        $this->assertIsArray($result);
        $total = count($result);
        $this->assertEquals(11, $total);
    }
    
}