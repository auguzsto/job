<?php
namespace Auguzsto\Example\Backup;

class LargerBackup
{
    /**
     * Simulating a backup.
     * @param int $time
     * @return void
     */
    public function go(int $time): void
    {   
        $begin = date("His");
        sleep($time);
        $finished = date("His");
        $backup = __DIR__ . "/../backup_$time$begin.txt";
        file_put_contents($backup,  "begin task: $begin\nfinished task: $finished\n");
    }
}