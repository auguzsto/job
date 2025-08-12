# About
The job executes the static method of a class from a given namespace.

# Simple example
Simulating a very time-consuming request.
```php
<?php
namespace Auguzsto\Job\Tests;

    class Request {

        public static function slow(): void {
            sleep(60);
        }
    }
```
Run this static method in the background with the job.
```php
<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Auguzsto\Job\Job;

    $job = new Job();
    $job->execute("Auguzsto\Job\Tests\Request::slow");
    print($worker->pid);
```
When executing the job, the PID of the background process is created and stored in the object.