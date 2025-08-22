<?php
namespace Auguzsto\Job;

use Auguzsto\Job\ProcessInterface;

interface JobInterface
{
    public function execute();
    public function process(): ProcessInterface;
    public function runner(): RunnerInterface;
}