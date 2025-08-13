<?php
namespace Auguzsto\Job;
use Auguzsto\Job\JobException;
use stdClass;

    class Job {

        private string $runner;
        public int $pid;

        private string $dirPid = __DIR__ . "/.pids";

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
                    $this->createPidFile($cmd);
                }
                
                return $this->isProcessRunning();
            } catch (JobException $th) {
                throw $th;
            }
        }

        public function getAllProcessInRunning(): array {
            $result = [];
            $pids = scandir($this->dirPid);
            foreach ($pids as $key => $pid) {
                if (is_dir($pid)) continue;

                $running = file_get_contents("{$this->dirPid}/$pid");
                
                $process = new stdClass();
                $process->pid = $pid;
                $process->running = $running;
                array_push($result, $process);
            }
            return $result;
        }

        private function isProcessRunning(): bool {
            if (file_exists($this->dirPid . "/{$this->pid}")) {
                return true;
            }

            return false;
        }

        private function createPidFile(string $cmd): void {
            if (!is_dir($this->dirPid)) {
                mkdir($this->dirPid);
            }

            file_put_contents("{$this->dirPid}/{$this->pid}", $cmd);
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

        private function checkClassExists(string $class): bool {
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