# How setup
Install package
```
composer require auguzsto/job
```
Initialize workers
```
vendor/bin/worker up
```
# Examples
### Simple example background task
```
php src/non-blocking.php
```

### Tasks example in concurrency
```
php src/concurrency.php
```