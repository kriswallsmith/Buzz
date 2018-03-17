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

        $middleware = new LoggerListener($logger);
        $middleware->preSend($request);
        $middleware->postSend($request, $response);
    }

    public function testInvalidLogger()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Buzz\Exception\InvalidArgumentException');
        } else {
            $this->setExpectedException('Buzz\Exception\InvalidArgumentException');
        }

        $middleware = new LoggerListener(array(1, 2, 3));
    }
}
