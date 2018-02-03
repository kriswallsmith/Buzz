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
        $listener = new CallbackListener(function() use (& $calls) {
            $calls[] = func_get_args();
        });

        $request = new Message\Request();
        $response = new Message\Response();

        $listener->preSend($request);
        $listener->postSend($request, $response);

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

        $listener = new CallbackListener(array(1, 2, 3));
    }
}
