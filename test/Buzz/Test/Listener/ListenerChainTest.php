<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\ListenerChain;
use Buzz\Message;

class ListenerChainTest extends \PHPUnit_Framework_TestCase
{
    public function testListeners()
    {
        $listener = new ListenerChain(array($this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock()));
        $this->assertEquals(1, count($listener->getListeners()));

        $listener->addListener($this->getMockBuilder('Buzz\Listener\ListenerInterface')->getMock());
        $this->assertEquals(2, count($listener->getListeners()));
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

        $listener = new ListenerChain(array($delegate));
        $listener->preSend($request);
        $listener->postSend($request, $response);
    }

    public function testChainWithException()
    {
        $delegate = $this->getMock('Buzz\Listener\ExceptionListenerInterface');
        $request = new Message\Request();
        $response = new Message\Response();
        $exception = new \Exception();

        $delegate->expects($this->once())
            ->method('preSend')
            ->with($request);
        $delegate->expects($this->once())
            ->method('onException')
            ->with($request, $exception);

        $listener = new ListenerChain(array($delegate));
        $listener->preSend($request);
        $listener->onException($request, $exception);
    }
}
