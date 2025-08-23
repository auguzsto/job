# About
The job executes the static method of a class from a given namespace.

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
Run this static method in the background with the job.
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
    $pid = $job->execute();
    echo $pid;
```
Execute a group jobs.
```php
<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Request;

    $jobs = new GroupJob([
        new Job(Request::class, "slow"),
        new Job(Request::class, "slowBy", [25]),
        new Job(Request::class, "slow"),
    ]);
    $pids = $jobs->execute();
    print_r($pids);
```
When executing the job, the PID of the background process is created and returned.