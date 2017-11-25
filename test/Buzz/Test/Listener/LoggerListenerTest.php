<?php

namespace Buzz\Test\Listener;

use Buzz\Exception\InvalidArgumentException;
use Buzz\Listener\LoggerListener;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class LoggerListenerTest extends TestCase
{
    public function testLogger()
    {
        $test = $this;
        $logger = function($line) use ($test) {
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
        if (method_exists($this, 'expectException')) {
            $this->expectException(InvalidArgumentException::class);
        } else {
            $this->setExpectedException(InvalidArgumentException::class);
        }

        $listener = new LoggerListener(array(1, 2, 3));
    }
}
