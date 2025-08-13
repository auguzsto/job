<?php
namespace Auguzsto\Job;
use Auguzsto\Job\JobException;

    class Job {

        private string $runner;
        public int $pid;

        public function __construct() {
            $this->setRunner(__DIR__ . "/runner");
        }

        public function execute(string $class, string $method, array $args = []): bool {
            try {
                if (!$this->checkRunnerExists()) {
                    throw new JobException("Runner not found");
                }

                if (!$this->checkClassExists($class)) {
                    throw new JobException("Class not found");
                }

                if (!$this->checkMethodExists($class, $method)) {
                    throw new JobException("Method not found");
                }

                $runner = $this->getRunner();
                $classmethod = escapeshellarg("$class::$method");
                $args = escapeshellarg(json_encode($args));;
                
                $cmd = "php $runner $classmethod $args > /dev/null 2>&1 & echo $!";
                exec($cmd, $output);

                if (!empty($output)) {
                    $this->pid = (int) $output[0];
                    return true;
                }
                
                return false;
            } catch (JobException $th) {
                throw $th;
            }
        }

        private function checkRunnerExists(): bool {
            if (file_exists($this->getRunner())) {
                return true;
            }

            return false;
        }

        private function checkMethodExists(string $class, string $method): bool {
            if (method_exists($class, $method)) {
                return true;
            }

            return false;
        }

        private function checkClassExists(string $classmethod): bool {
            $class = explode("::", $classmethod)[0];
            if (class_exists($class)) {
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