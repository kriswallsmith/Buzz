<?php

namespace Buzz;

class ClassLoader
{
  static protected $instance;

  protected $path;

  static public function getInstance()
  {
    if (null === static::$instance)
    {
      static::$instance = new static();
    }

    return static::$instance;
  }

  protected function __construct()
  {
    $this->path = realpath(__DIR__.'/..');
  }

  public function register()
  {
    spl_autoload_register(array($this, 'autoload'));
  }

  public function unregister()
  {
    spl_autoload_unregister(array($this, 'autoload'));
  }

  public function autoload($class)
  {
    if (0 === strpos($class, 'Buzz\\'))
    {
      require $this->path.'/'.str_replace('\\', '/', $class).'.php';
      return true;
    }
  }
}
