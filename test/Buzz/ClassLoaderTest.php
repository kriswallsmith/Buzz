<?php

namespace Buzz;

use Buzz\Client\Mock;
use Buzz\Message;

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
