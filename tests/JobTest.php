<?php

use Auguzsto\Job\Job;
use Auguzsto\Job\JobException;
use Auguzsto\Job\Tests\Consumer;
use Auguzsto\Job\Tests\Request;
use PHPUnit\Framework\TestCase;

    final class JobTest extends TestCase {

        public function testRunningMethodsInBackground(): void {
            $job = new Job();
            $result = $job->execute(Request::class, "slow");
            $this->assertTrue($result);
        }

        public function testCreatePidWhenExecuteMethodInBackground(): void {
            $job = new Job();
            $job->execute(Request::class, "slow");
            $this->assertIsInt($job->pid);
        }

        public function testAbortIfMethodNotExists(): void {
            $this->expectException(JobException::class);
            $this->expectExceptionMessage("Method not found");
            $job = new Job();
            $job->execute(Request::class, "methodNotExists");
        }

        public function testAbortIfRunnerNotExists(): void {
            $this->expectException(JobException::class);
            $this->expectExceptionMessage("Runner not found");
            $job = new Job();
            $job->setRunner("");
            $job->execute(Request::class, "slow");
        }

        public function testRunningJobWithArgs(): void {
            $job = new Job();
            $result = $job->execute(Request::class, "slowBy", [35]);
            $this->assertTrue($result);
        }

        public function testGetAllProcessInRunning(): void {
            $job = new Job();
            $result = $job->getAllProcessInRunning();
            $this->assertIsArray($result);
            $this->assertObjectHasProperty("pid", $result[0]);
            $this->assertObjectHasProperty("running", $result[0]);
        }
    }