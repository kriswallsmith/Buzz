<?php

namespace Buzz;

class ClassLoader
{
    static protected $instance;

    protected $path;

    static public function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    static public function register()
    {
        spl_autoload_register(array(static::getInstance(), 'autoload'));
    }

    static public function unregister()
    {
        spl_autoload_unregister(array(static::getInstance(), 'autoload'));
    }

    protected function __construct()
    {
        $this->path = realpath(__DIR__.'/..');
    }

    public function autoload($class)
    {
        if (class_exists($class, false) || interface_exists($class, false)) {
            return true;
        }

        if (0 === strpos($class, 'Buzz\\')) {
            set_error_handler(array($this, 'handleIncludeError'));
            $exists = include $this->path.'/'.str_replace('\\', '/', $class).'.php';
            restore_error_handler();
            return $exists;
        }
    }

    public function handleIncludeError($errno, $errstr, $errfile, $errline, $errcontext)
    {
        if (0 !== strpos($errstr, 'include')) {
            return false;
        }
    }
}
