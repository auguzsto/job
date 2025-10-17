<?php

use Auguzsto\Job\Exceptions\IncludeNotExistsException;
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
    private const string LOG_ERROR = "/tmp/php-job-error.log";

    public function setUp(): void
    {
        if (file_exists(self::LOG_ERROR)) {
            unlink(self::LOG_ERROR);
        }
    }

    public function testRunningJobMethodStaticWithArgs(): void
    {
        $time = 1;
        $wait = $time+1;
        $job = new Job(Backup::class, "largerStaticWithArgs", [$time]);
        $worker = $job->execute();
        sleep($wait);
        $this->assertIsInt($worker);

        $result = file_exists("backup_static_with_args_large_$time.txt");
        $this->assertTrue($result);
    }

    public function testRunningJobMethodStaticWithoutArgs(): void
    {
        $job = new Job(Backup::class, "largerStaticWithoutArgs");
        $worker = $job->execute();
        sleep(2);
        $this->assertIsInt($worker);

        $result = file_exists("backup_static_without_args_large_1.txt");
        $this->assertTrue($result);
    }

    public function testRunningJobMethodNotStaticWithArgs(): void
    {
        $time = 1;
        $wait = $time+1;
        $job = new Job(Backup::class, "largerWithArgs", [$time]);
        $worker = $job->execute();
        $this->assertIsInt($worker);
        sleep($wait);

        $result = file_exists("backup_large_with_args_$time.txt");
        $this->assertTrue($result);
    }

    public function testRunningJobMethodNotStaticWithoutArgs(): void
    {
        $job = new Job(Backup::class, "larger");
        $worker = $job->execute();
        $this->assertIsInt($worker);
        sleep(2);

        $result = file_exists("backup_large_1.txt");
        $this->assertTrue($result);
    }

    public function testJobRunningMethodNotStaticAndArgsWithObject(): void
    {
        $time = new Time(2);
        $wait = $time->get()+1;
        $job = new Job(Backup::class, "big", [$time]);
        $worker = $job->execute();
        $this->assertIsInt($worker);

        sleep($wait);
        $result = file_exists("backup_big_with_args_object_{$time->get()}.txt");
        $this->assertTrue($result);
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
        
        $workers = [];
        $jobs = [
            new Job(Backup::class, "largerWithArgs", [1]),
            new Job(Backup::class, "largerWithArgs", [5]),
            new Job(Backup::class, "largerWithArgs", [8]),
        ];
        
        foreach ($jobs as $key => $job) {
            $worker = $job->execute();
            array_push($workers, $worker);
        }

        $this->assertIsArray($workers);
        $this->assertEquals(3, count($workers));
        sleep(2);
        $result = file_exists("backup_large_with_args_1.txt");
        $this->assertTrue($result);

    }

    public function testAbortIfMethodNotExists(): void
    {
        $this->expectException(MethodNotExistsException::class);
        $this->expectExceptionMessage("Method not found");
        $job = new Job(Request::class, "methodNotExists");
        $job->execute();
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
        $job = new Job(ClassWithError::class, "here", ["error"]);
        $worker = $job->execute();
        $dirworker = Worker::DIR;
        sleep(3);
        
        $result = file_exists("$dirworker/$worker");
        $this->assertTrue($result, "Worker was rebuilt");
    }

    public function testBinGeneratesLogWhenExecutionFails(): void
    {
        $job = new Job(ClassWithError::class, "here", ["error"]);
        $job->execute();
        sleep(1);
        $this->assertTrue(file_exists(self::LOG_ERROR), "Error log");
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

    public function testThrowIncludeNotExistsException(): void
    {
        $this->expectException(IncludeNotExistsException::class);

        $job = new Job("Includes", "run", ["testing"]);
        $job->include(__DIR__ . "/Mocks/IncludeNotExists.php");
        $job->execute();
    }

    public function testExecuteClassAndMethodByIncludeInjection(): void
    {
        $job = new Job("Includes", "run", ["testing"]);
        $job->include(__DIR__ . "/Mocks/Includes.php");
        $job->execute();
        sleep(1);
        
        $result = file_exists("include_tests.txt");
        $this->assertTrue($result);
    }

    public static function tearDownAfterClass(): void
    {
        Worker::down();
    }
    
}