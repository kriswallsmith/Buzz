<?php

spl_autoload_register(function($class)
{
    if (0 === strpos($class, 'Buzz\\')) {
        $file = __DIR__ . '/../lib/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
});
