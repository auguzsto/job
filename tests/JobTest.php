<?php

use Auguzsto\Job\Job;
use Auguzsto\Job\JobException;
use PHPUnit\Framework\TestCase;

    final class JobTest extends TestCase {

        public function testRunningMethodsInBackground(): void {
            $job = new Job();
            $result = $job->execute("Auguzsto\Job\Tests\Request::slow");
            $this->assertTrue($result);
        }

        public function testCreatePidWhenExecuteMethodInBackground(): void {
            $job = new Job();
            $job->execute("Auguzsto\Job\Tests\Request::slow");
            $this->assertIsInt($job->pid);
        }

        public function testAbortIfClassNotExists(): void {
            $this->expectException(JobException::class);
            $this->expectExceptionMessage("Class not found");
            $job = new Job();
            $job->execute("Auguzsto\Job\Tests\ClassNotExists::methodNotExists");
        }

        public function testAbortIfStaticMethodNotExists(): void {
            $this->expectException(JobException::class);
            $this->expectExceptionMessage("Static method not found");
            $job = new Job();
            $job->execute("Auguzsto\Job\Tests\Request::methodNotExists");
        }

        public function testAbortIfRunnerNotExists(): void {
            $this->expectException(JobException::class);
            $this->expectExceptionMessage("Runner not found");
            $job = new Job();
            $job->setRunner("");
            $job->execute("Auguzsto\Job\Tests\Request::slow");
        }
    }