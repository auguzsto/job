<?php

use Auguzsto\Job\Job;
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
    }