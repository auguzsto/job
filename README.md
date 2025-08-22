# About
The job executes the static method of a class from a given namespace.

# Install
```sh
composer require auguzsto/job:0.0.1
```

# Simple example
Simulating a very time-consuming request.
```php
<?php
namespace Auguzsto\Job\Tests;

    class Request {

        public static function slow(): void {
            sleep(60);
        }

        public static function slowBy(int $seconds): void {
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
    $job->execute();
    echo $job->process->getPid();
```
When executing the job, the PID of the background process is created and stored in the object process.