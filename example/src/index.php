<?php

use Auguzsto\Example\Queue\Consume;
use Auguzsto\Example\Tasks\Backup;
use Auguzsto\Job\Job;

    require_once __DIR__ . "/../vendor/autoload.php";
    
    $job = new Job();

    /// Simulating a backup in background.
    $job->execute(Backup::class, "large");

    /// Simulating a queue consumer.
    $job->execute(Consume::class, "listen");