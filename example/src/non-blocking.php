<?php

use Auguzsto\Example\Backup\LargerBackup;
use Auguzsto\Job\Job;

require_once __DIR__ . "/../vendor/autoload.php";

$job = new Job(LargerBackup::class, "go", [3]);
$worker = $job->execute();
print "Task sent to worker $worker" . PHP_EOL;