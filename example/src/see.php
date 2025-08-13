<?php

use Auguzsto\Job\Job;

    require_once __DIR__ . "/../vendor/autoload.php";

    $job = new Job();
    print_r($job->process->running());