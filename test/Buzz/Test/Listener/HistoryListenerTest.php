<?php

namespace Buzz\Test\Listener;

use Buzz\Listener\HistoryListener;
use Buzz\Message;
use PHPUnit\Framework\TestCase;

class HistoryListenerTest extends TestCase
{
    private $journal;
    private $middleware;

    protected function setUp()
    {
        $this->journal = $this->getMockBuilder('Buzz\Listener\History\Journal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new HistoryListener($this->journal);
    }

    public function testHistory()
    {
        $request = new Message\Request();
        $response = new Message\Response();

        $this->journal->expects($this->once())
            ->method('record')
            ->with($request, $response, $this->isType('float'));

        $this->listener->preSend($request);
        $this->listener->postSend($request, $response);
    }

    public function testGetter()
    {
        $this->assertSame($this->journal, $this->listener->getJournal());
    }
}
