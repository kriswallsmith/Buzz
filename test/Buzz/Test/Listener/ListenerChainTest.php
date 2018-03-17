<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\ListenerChain;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class ListenerChainTest extends TestCase
{
    public function testListeners()
    {
        $middleware = new ListenerChain(array($this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock()));
        $this->assertEquals(1, count($middleware->getListeners()));

        $middleware->addListener($this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock());
        $this->assertEquals(2, count($middleware->getListeners()));
    }

    public function testChain()
    {
        $delegate = $this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock();
        $request = new Message\Request();
        $response = new Message\Response();

        $delegate->expects($this->once())
            ->method('preSend')
            ->with($request);
        $delegate->expects($this->once())
            ->method('postSend')
            ->with($request, $response);

        $middleware = new ListenerChain(array($delegate));
        $middleware->preSend($request);
        $middleware->postSend($request, $response);
    }
}
