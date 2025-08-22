<?php
namespace Auguzsto\Job\Tests;

use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Job;
use PHPUnit\Framework\TestCase;

class GroupJobTest extends TestCase
{
    public function testRunningJobsInGroup(): void {
        $groupJob = new GroupJob([
            new Job(Request::class, "slow"),
            new Job(Request::class, "slow"),
            new Job(Request::class, "slow"),
        ]);
        
        $pids = $groupJob->execute();
        $this->assertIsArray($pids);
        $this->assertEquals(3, count($pids));
    } 
}