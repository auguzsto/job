<?php
namespace Auguzsto\Job;

    class Job {

        private string $runner;
        public int $pid;

        public function __construct() {
            $this->setRunner(__DIR__ . "/runner.php");
        }

        public function execute(string $classmethod, array $args = []): bool {
           $runner = $this->getRunner();
            $classmethod = escapeshellarg($classmethod);
            $args = escapeshellarg(json_encode($args));;

            exec("php $runner $classmethod $args > /dev/null 2>&1 & echo $!", $output);

            if (!empty($output)) {
                $this->pid = (int) $output[0];
                return true;
            }
            
            return false;
        }

        public function setRunner(string $runner): void {
            $this->runner = $runner;
        }

        public function getRunner(): string {
            return $this->runner;
        }
    }