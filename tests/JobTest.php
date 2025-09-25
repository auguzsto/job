<?php

use Auguzsto\Job\Job;
use Auguzsto\Job\Worker;
use Auguzsto\Job\GroupJob;
use PHPUnit\Framework\TestCase;
use Auguzsto\Job\Tests\Mocks\Time;
use Auguzsto\Job\Tests\Mocks\Backup;
use Auguzsto\Job\Tests\Mocks\Request;
use Auguzsto\Job\Tests\Mocks\ClassWithError;
use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\Exceptions\WorkerNotAvailableException;
use Auguzsto\Job\Exceptions\WorkerIdExceedsMaximumLimitException;

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
    public function testReturnArrayWithActiveWorkers(): void
    {
        $result = Worker::workers();
        $this->assertIsArray($result);
        $total = count($result);
        $this->assertEquals(11, $total);
    }

    public function testRebuildWorkerThatFailedExecution(): void
    {
        unlink("/tmp/php-job-error.log");
        $job = new Job(ClassWithError::class, "here", ["error"]);
        $worker = $job->execute();
        $dirworker = Worker::DIR;
        sleep(3);
        
        $result = file_exists("$dirworker/$worker");
        $this->assertTrue($result, "Worker was rebuilt");
    }

    public function testBinGeneratesLogWhenExecutionFails(): void
    {
        $result = file_exists("/tmp/php-job-error.log");
        $this->assertTrue($result);
    }

    public function testThrowWorkerIdExceedsMaximumLimit(): void
    {
        $this->expectException(WorkerIdExceedsMaximumLimitException::class);
        Worker::up(11);
    }

    public function testThrowWorkerNotAvailableException(): void
    {
        $this->expectException(WorkerNotAvailableException::class);
        Worker::up(0);
    }

    public function testDownAllActiveWorkers(): void
    {
        $result = Worker::down();
        $this->assertIsArray($result);
        $total = count($result);
        $this->assertEquals(11, $total);
    }

    public function testANewWorkerIsOnlyConstructedIfNecessary(): void
    {
        for ($i = 0; $i <= 5; $i++) { 
            $job = new Job(Request::class, "slowBy", [4]);
            $job->execute();
            sleep(2);
        }

        $result = file_exists(Worker::DIR . "/0");
        $this->assertTrue($result);

        $result = file_exists(Worker::DIR . "/1");
        $this->assertTrue($result);

        $result = file_exists(Worker::DIR . "/2");
        $this->assertTrue($result);

        $result = file_exists(Worker::DIR . "/3");
        $this->assertFalse($result);
    }

    public static function tearDownAfterClass(): void
    {
        Worker::down();
    }
    
}