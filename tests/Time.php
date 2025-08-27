<?php
namespace Auguzsto\Job\Tests;
use Auguzsto\Job\Tests\TimeInterface;

class Time implements TimeInterface
{
    private int $time;

    public function __construct(int $time)
    {
        $this->set($time);
    }

    public function set(int $time): void
    {
        $this->time = $time;
    }

    public function get(): int
    {
        return $this->time;
    }
}