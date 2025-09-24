<?php
namespace Auguzsto\Job\Tests\Mocks;

class ClassWithError
{
    public static function here(): void
    {
        try {
            $error = 0/0;
        } catch (\Throwable $th) {
            file_put_contents(__DIR__ . "/../../error_in_ClassWithError.txt", $th->getMessage());
            throw $th;
        }
    }
}