# About
A job executes a method in the background. If there are too many jobs for too few workers, there will be competition. When a worker is free, the job will dispatch the task to that free worker.

A PID is created for each registered worker. The worker checks for tasks to be performed. In other words, worker = PID.

<b>By default, the worker limit is 10.</b>

<div style="text-align:center; margin-bottom:15px;">
<img src="https://github.com/auguzsto/job/blob/main/images/design.png?raw=true">
</div>

# Requirements
- PHP >= 8.3
- Linux

# Install
```sh
composer require auguzsto/job
```

# Simple example
Simulating a very time-consuming request.
```php
<?php
namespace Auguzsto\Job\Tests;

    class Request 
    {

        public static function slow(): void 
        {
            sleep(60);
        }

        public static function slowBy(int $seconds): void 
        {
            sleep($seconds);
        }
    }
```
Run this method in the background with the job.
```php
<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Request;

    $job = new Job(Request::class, "slow");
    $job->execute();
```

With args.
```php
<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Request;

    $job = new Job(Request::class, "slowBy", [35]);
    $worker = $job->execute();
    echo $worker;
```
Execute by include injection.
```php
<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Auguzsto\Job\Job;

    $job = new Job("Request", "slowBy", [35]);
    $job->include(__DIR__ . "/Tests/Request.php");
    $worker = $job->execute();
    echo $worker;
```
When the job sends a task to the worker, the id of this worker is returned.

# See logs erros
You can read logs error in 
- /tmp/php-job-error.log

Example
```
cat /tmp/php-job-error.log
```