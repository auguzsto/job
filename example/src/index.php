<?php

use Auguzsto\Example\Tasks\Backup;
use Auguzsto\Job\Job;

    require_once __DIR__ . "/../vendor/autoload.php";
    
    $job = new Job();
    $result = $job->execute(Backup::class, "large");
    print($job->pid);