<?php

use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\Exceptions\RunnerNotExistsException;
use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Request;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{
    public function testCreatePidWhenExecuteMethodInBackground(): void
    {
        $job = new Job(Request::class, "slow");
        $this->assertIsInt($job->execute());
    }

    public function testAbortIfMethodNotExists(): void
    {
        $this->expectException(MethodNotExistsException::class);
        $this->expectExceptionMessage("Method not found");
        $job = new Job(Request::class, "methodNotExists");
        $job->execute();
    }

    public function testAbortIfRunnerNotExists(): void
    {
        $this->expectException(RunnerNotExistsException::class);
        $job = new Job(Request::class, "slow");
        $job->runner()->setBin("");
        $job->execute();
    }

    public function testRunningJobWithArgs(): void
    {
        $job = new Job(Request::class, "slowBy", [35]);
        $this->assertIsInt($job->execute());
    }

    public function testGetAllProcessInRunning(): void
    {
        $job = new Job();
        $result = $job->process()->running();
        $this->assertIsArray($result);
        $this->assertObjectHasProperty("pid", $result[0]);
        $this->assertObjectHasProperty("running", $result[0]);
    }
}