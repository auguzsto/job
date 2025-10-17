<?php

class Includes
{
    public function run(string $name): void
    {
        file_put_contents("include_tests.txt", $name);
    }
}