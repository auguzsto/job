<?php
namespace Auguzsto\Job\Tests;

interface TimeInterface
{
    public function set(int $id): void;
    public function get(): int;
}