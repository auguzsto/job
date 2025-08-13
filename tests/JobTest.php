<?php

use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\Exceptions\RunnerNotExistsException;
use Auguzsto\Job\Job;
use Auguzsto\Job\JobException;
use Auguzsto\Job\Tests\Request;
use PHPUnit\Framework\TestCase;

final class JobTest extends TestCase
{

    public function testRunningMethodsInBackground(): void
    {
        $job = new Job();
        $job->execute(Request::class, "slow");
        $this->assertTrue($job->process->isRunning());
    }

    public function testCreatePidWhenExecuteMethodInBackground(): void
    {
        $job = new Job();
        $job->execute(Request::class, "slow");
        $this->assertIsInt($job->process->getPid());
    }

    public function testAbortIfMethodNotExists(): void
    {
        $this->expectException(MethodNotExistsException::class);
        $this->expectExceptionMessage("Method not found");
        $job = new Job();
        $job->execute(Request::class, "methodNotExists");
    }

    public function testAbortIfRunnerNotExists(): void
    {
        $this->expectException(RunnerNotExistsException::class);
        $job = new Job();
        $job->runner->setBin("");
        $job->execute(Request::class, "slow");
    }

    public function testRunningJobWithArgs(): void
    {
        $job = new Job();
        $job->execute(Request::class, "slowBy", [35]);
        $this->assertTrue($job->process->isRunning());
    }

    public function testGetAllProcessInRunning(): void
    {
        $job = new Job();
        $result = $job->process->running();
        $this->assertIsArray($result);
        $this->assertObjectHasProperty("pid", $result[0]);
        $this->assertObjectHasProperty("running", $result[0]);
    }
}