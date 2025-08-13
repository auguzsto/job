<?php
namespace Auguzsto\Job;

use Auguzsto\Job\RunnerInterface;
use Auguzsto\Job\Exceptions\RunnerNotExistsException;

class Runner implements RunnerInterface
{
    private string $bin;

    public function __construct() {
        $this->setBin(__DIR__ . "/runner");
    }

    public function bin(): string {
        return $this->bin;
    }

    public function setBin(string $path): void {
        if (!file_exists($path)) {
            throw new RunnerNotExistsException("File runner not found in {$this->bin()}");
        }

        $this->bin = $path;
    }
}