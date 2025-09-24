<?php
namespace Auguzsto\Job\Tests\Mocks;

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