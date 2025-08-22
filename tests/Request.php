<?php
namespace Auguzsto\Job\Tests;

class Request
{

    public static function slow(): void
    {
        sleep(15);
    }

    public static function slowBy(int $seconds): void
    {
        sleep($seconds);
    }
}