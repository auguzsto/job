<?php
namespace Auguzsto\Job;

interface JobInterface
{
    public function include(string $path): void;
    public function execute(): int;
}