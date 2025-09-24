<?php
namespace Auguzsto\Job\Tests\Mocks;
use Auguzsto\Job\Tests\Mocks\TimeInterface;

class Backup
{
    public static function larger(int $time): void
    {
        sleep($time);
        file_put_contents("backup_large_$time.txt", date("H:i:s"));
    }

    public function big(TimeInterface $time): void
    {   
        $timesleep = $time->get();
        sleep($timesleep);
        file_put_contents("backup_big_$timesleep.txt", date("H:i:s"));
    }
}