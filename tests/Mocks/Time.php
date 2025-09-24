<?php
namespace Auguzsto\Job\Tests\Mocks;
use Auguzsto\Job\Tests\Mocks\TimeInterface;

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