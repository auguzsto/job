# About
The job executes the static method of a class from a given namespace.

# Requirements
- PHP >= 8.3
- Linux

# Install
```sh
composer require auguzsto/job
```
```
php vendor/bin/worker up
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
    $queue = $job->execute();
    echo $queue;
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
    $queues = $jobs->execute();
    print_r($queues);
```
When executing the job, the PID of the background process is created and returned.

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
Default 10 workers.
```sh
php vendor/bin/worker up
```
Or choose your quantity (but be careful, all workers are hung on PIDs). See the image below.

```sh
php vendor/bin/worker up 20
```

<div style="text-align:center">
<img src="https://github.com/auguzsto/job/blob/1.0.0/images/workers.png?raw=true">
</div>

### Down
You can also down workers. Remember that for the Job class to work correctly, it needs to have active workers.
```
php vendor/bin/worker down
```

### Working
<div style="text-align:center">
<img src="https://github.com/auguzsto/job/blob/1.0.0/images/image.png?raw=true">
</div>