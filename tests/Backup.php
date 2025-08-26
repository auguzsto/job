<?php
namespace Auguzsto\Job\Tests;

class Backup
{
    public static function larger(int $time): void
    {
        sleep($time);
        file_put_contents("backup_large_$time.txt", date("H:i:s"));
    }
}