<?php

declare(strict_types=1);

namespace Buzz\Test\Unit\Middleware;

use Buzz\Middleware\HistoryMiddleware;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;

class HistoryMiddlewareTest extends TestCase
{
    private $journal;

    /** @var HistoryMiddleware */
    private $middleware;

    protected function setUp(): void
    {
        $this->journal = $this->getMockBuilder('Buzz\Middleware\History\Journal')
            ->disableOriginalConstructor()
            ->getMock();

        $this->middleware = new HistoryMiddleware($this->journal);
    }

    public function testHistory()
    {
        $request = new Request('GET', '/');
        $response = new Response();

        $this->journal->expects($this->once())
            ->method('record')
            ->with($request, $response, $this->isType('float'));

        $this->middleware->handleRequest($request, function () {
        });
        $this->middleware->handleResponse($request, $response, function () {
        });
    }

    public function testGetter()
    {
        $this->assertSame($this->journal, $this->middleware->getJournal());
    }
}
