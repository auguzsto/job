<?php
namespace Auguzsto\Job\Tests\Mocks;
use Auguzsto\Job\Tests\Mocks\TimeInterface;

class Backup
{
    public static function largerStaticWithArgs(int $time): void
    {
        sleep($time);
        file_put_contents("backup_static_with_args_large_$time.txt", date("H:i:s"));
    }

    public static function largerStaticWithoutArgs(): void
    {
        sleep(1);
        file_put_contents("backup_static_without_args_large_1.txt", date("H:i:s"));
    }

    public function largerWithArgs(int $time): void
    {
        sleep($time);
        file_put_contents("backup_large_with_args_$time.txt", date("H:i:s"));
    }
    
    public function larger(): void
    {
        sleep(1);
        file_put_contents("backup_large_1.txt", date("H:i:s"));
    }

    public function big(TimeInterface $time): void
    {   
        $timesleep = $time->get();
        sleep($timesleep);
        file_put_contents("backup_big_with_args_object_$timesleep.txt", date("H:i:s"));
    }

    public static function small(): void
    {
        sleep(1);
        file_put_contents("backup_small_1.txt", date("H:i:s"));
    }

    public function smallBy(int $time): void
    {
        sleep($time);
        file_put_contents("backup_smallBy_$time.txt", date("H:i:s"));
    }
}