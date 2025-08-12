<?php

    if ($argc > 3) {
        exit();
    }

    $callable = $argv[1];
    $args = [];

    if (isset($argv[2])) {
        $args = json_decode($argv[2], true);
    }

    $autoload = __DIR__."/../vendor/autoload.php";

    if (!file_exists($autoload)) {
        exit("Autoload not found!");
    }

    require_once $autoload;

    if (!is_callable($callable)) {
        exit("Method is not callable!");
    }
    
    call_user_func_array($callable, $args);