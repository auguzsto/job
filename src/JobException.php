<?php
namespace Auguzsto\Job;

    class JobException extends \Exception {

        public function __construct(string $message = "", int $code = 0) {
            $this->message = $message;
            $this->code = $code;
        }
    }