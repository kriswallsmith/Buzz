<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\ListenerChain;
use Buzz\Message;

class ListenerChainTest extends \PHPUnit_Framework_TestCase
{
    public function testListeners()
    {
        $listener = new ListenerChain(array($this->getMock('Buzz\Listener\ListenerInterface')));
        $this->assertEquals(1, count($listener->getListeners()));

        $listener->addListener($this->getMock('Buzz\Listener\ListenerInterface'));
        $this->assertEquals(2, count($listener->getListeners()));
    }

    public function testChain()
    {
        $delegate = $this->getMock('Buzz\Listener\ListenerInterface');
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
}
