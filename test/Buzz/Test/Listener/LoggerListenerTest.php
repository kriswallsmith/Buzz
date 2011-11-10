<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\LoggerListener;
use Buzz\Message;

class LoggerListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testLogger()
    {
        $test = $this;
        $logger = function($line) use($test) {
            $test->assertRegExp('~^Sent "GET http://google.com/" in \d+ms$~', $line);
        };

        $request = new Message\Request();
        $request->fromUrl('http://google.com/');
        $response = new Message\Response();

        $listener = new LoggerListener($logger);
        $listener->preSend($request);
        $listener->postSend($request, $response);
    }

    public function testInvalidLogger()
    {
        $this->setExpectedException('InvalidArgumentException');
        $listener = new LoggerListener(array(1, 2, 3));
    }
}
