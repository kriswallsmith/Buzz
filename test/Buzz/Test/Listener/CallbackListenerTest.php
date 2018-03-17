<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\CallbackListener;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class CallbackListenerTest extends TestCase
{
    public function testCallback()
    {
        $calls = array();
        $middleware = new CallbackListener(function() use (& $calls) {
            $calls[] = func_get_args();
        });

        $request = new Message\Request();
        $response = new Message\Response();

        $middleware->preSend($request);
        $middleware->postSend($request, $response);

        $this->assertEquals(array(
            array($request),
            array($request, $response),
        ), $calls);
    }

    public function testInvalidCallback()
    {
        if (method_exists($this, 'expectException')) {
            $this->expectException('Buzz\Exception\InvalidArgumentException');
        } else {
            $this->setExpectedException('Buzz\Exception\InvalidArgumentException');
        }

        $middleware = new CallbackListener(array(1, 2, 3));
    }
}
