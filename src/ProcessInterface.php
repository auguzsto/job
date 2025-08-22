<?php
namespace Auguzsto\Job;

interface ProcessInterface
{
    public const DIR = __DIR__ . "/.pids";
    public function setPid(int $pid): void;
    public function getPid(): int;
    public function running(): array;
    public function createFile(int $pid, string $content): void;
}