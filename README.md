# About
A job executes a method in the background. If there are too many jobs for too few workers, there will be competition. When a worker is free, the job will dispatch the task to that free worker.

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
Execute a group jobs.
```php
<?php
require_once __DIR__ . "/../vendor/autoload.php";

use Auguzsto\Job\GroupJob;
use Auguzsto\Job\Job;
use Auguzsto\Job\Tests\Request;
use Auguzsto\Job\Tests\Backup;
use Auguzsto\Job\Tests\Time;

    $jobs = new GroupJob([
        new Job(Request::class, "slow"),
        new Job(Request::class, "slowBy", [25]),
        new Job(Backup::class, "big", [new Time(1)]),
        new Job(Request::class, "slow"),
    ]);
    $workers = $jobs->execute();
    print_r($workers);
```
When the job sends a task to the worker, the id of this worker is returned.

# See logs erros
You can read logs error in 
- /tmp/php-bin-error.log 
- /tmp/php-worker-error.log

Example
```
cat /tmp/php-worker-error.log
```

# Workers
### Up
A PID is created for each registered worker. The worker checks for tasks to be performed. In other words, worker = PID.

By default, the worker limit is 10.

<div style="text-align:center">
<img src="https://github.com/auguzsto/job/blob/1.0.0/images/workers.png?raw=true">
</div>

### Down
You can also down workers. Remember that for the Job class to work correctly, it needs to have active workers.
```
vendor/bin/worker down
```

### Working
<div style="text-align:center">
<img src="https://github.com/auguzsto/job/blob/main/images/design.png?raw=true">
</div>