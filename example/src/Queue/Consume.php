<?php
namespace Auguzsto\Example\Queue;

    class Consume {

        public static function listen(): never {
            while(true) {
                sleep(5);
            }
        }
    }