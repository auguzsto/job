<?php
namespace Auguzsto\Job;

class GroupJob implements JobInterface
{

    private array $jobs;

    public function __construct(array $job)
    {
        $this->jobs = $job;
    }

    public function execute(): array
    {
        $queues = [];
        foreach ($this->jobs as $key => $job) {
            $queue = $job->execute();
            array_push($queues, $queue);
        }
        return $queues;
    }
}