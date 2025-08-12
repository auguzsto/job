<?php
namespace Auguzsto\Job;
use Auguzsto\Job\JobException;

    class Job {

        private string $runner;
        public int $pid;

        public function __construct() {
            $this->setRunner(__DIR__ . "/runner");
        }

        public function execute(string $classmethod, array $args = []): bool {
            try {
                if (!$this->checkRunnerExists()) {
                    throw new JobException("Runner not found");
                }

                if (!$this->checkClassExists($classmethod)) {
                    throw new JobException("Class not found");
                }

                if (!$this->checkStaticMethodExists($classmethod)) {
                    throw new JobException("Static method not found");
                }

                $runner = $this->getRunner();
                $classmethod = escapeshellarg($classmethod);
                $args = escapeshellarg(json_encode($args));;
                
                $cmd = "php $runner $classmethod $args > /dev/null 2>&1 & echo $!";
                print($cmd);
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

        private function checkStaticMethodExists(string $classmethod): bool {
            $class = explode("::", $classmethod)[0];
            $method = explode("::", $classmethod)[1];

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