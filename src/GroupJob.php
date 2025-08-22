<?php
namespace Auguzsto\Job;

use Auguzsto\Job\Runner;
use Auguzsto\Job\Process;

class GroupJob implements JobInterface
{

    private array $jobs;

    public function __construct(array $job)
    {
        $this->jobs = $job;
    }

    public function runner(): RunnerInterface
    {
        return new Runner();
    }

    public function process(): ProcessInterface
    {
        return new Process();
    }

    public function execute(): array
    {
        $pids = [];
        foreach ($this->jobs as $key => $job) {
            $pid = $job->execute();
            array_push($pids, $pid);
        }
        return $pids;
    }
}