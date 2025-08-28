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
        $workers = [];
        foreach ($this->jobs as $key => $job) {
            $worker = $job->execute();
            array_push($workers, $worker);
        }
        return $workers;
    }
}