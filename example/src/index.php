<?php

use Auguzsto\Job\Job;

    require_once __DIR__ . "/../vendor/autoload.php";
    
    $job = new Job();
    $result = $job->execute("Auguzsto\Example\Tasks\Backup::large");
    print($job->pid);