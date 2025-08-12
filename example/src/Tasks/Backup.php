<?php
namespace Auguzsto\Example\Tasks;

    class Backup {

        public static function large(): void {
            sleep(5);
            file_put_contents("example.backup.txt", "Backup success after 5 seconds");
        }
    }