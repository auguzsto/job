<?php
namespace Auguzsto\Job\Tests;

    class Request {

        public static function slow(): void {
            sleep(15);
        }
    }