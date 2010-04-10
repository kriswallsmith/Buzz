<?php

namespace Buzz;

use Buzz;
use Buzz\Client\Mock;
use Buzz\Message;

require_once __DIR__.'/../../lib/Buzz/ClassLoader.php';
Buzz\ClassLoader::register();

require_once 'PHPUnit/Framework/TestCase.php';

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
  public function testAutoloadReturnsTrueIfClassExists()
  {
    $this->assertTrue(ClassLoader::getInstance()->autoload('Buzz\Browser'));
  }

  public function testAutoloadReturnsFalseIfClassDoesNotExist()
  {
    $this->assertFalse(ClassLoader::getInstance()->autoload('Buzz\Invalid'));
  }
}
