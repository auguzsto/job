<?php

use Auguzsto\Example\Queue\Consume;
use Auguzsto\Example\Tasks\Backup;
use Auguzsto\Job\Job;

    require_once __DIR__ . "/../vendor/autoload.php";
    
    /// Simulating a backup in background.
    $job = new Job(Backup::class, "large");
    $job->execute();

    /// Simulating a queue consumer.
    $job = new Job(Consume::class, "listen");
    $job->execute();