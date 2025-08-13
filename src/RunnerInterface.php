<?php
namespace Auguzsto\Job;

interface RunnerInterface
{
    public function setBin(string $path): void;
    public function bin(): string;
}