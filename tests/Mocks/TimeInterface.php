<?php
namespace Auguzsto\Job\Tests\Mocks;

interface TimeInterface
{
    public function set(int $id): void;
    public function get(): int;
}