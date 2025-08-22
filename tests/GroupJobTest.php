<?php
namespace Auguzsto\Job\Tests;

use Auguzsto\Job\Exceptions\MethodNotExistsException;
use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Job;
use PHPUnit\Framework\TestCase;

class GroupJobTest extends TestCase
{
    public function testRunningJobsInGroup(): void
    {
        $groupJob = new GroupJob([
            new Job(Request::class, "slow"),
            new Job(Request::class, "slow"),
            new Job(Request::class, "slow"),
        ]);

        $pids = $groupJob->execute();
        $this->assertIsArray($pids);
        $this->assertEquals(3, count($pids));
    }

    public function testAbortAllJobsIfAnyClassMethodNotExists(): void
    {
        $this->expectException(MethodNotExistsException::class);
        $jobs = new GroupJob([
            new Job(Request::class, "x"),
            new Job(Request::class, "y"),
            new Job(Request::class, "z"),
        ]);
        $jobs->execute();
    }
}