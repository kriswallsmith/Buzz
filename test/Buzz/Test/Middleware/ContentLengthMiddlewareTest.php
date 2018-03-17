<?php

namespace Buzz\Test\Listener;

use Buzz\Middleware\ContentLengthMiddleware;
use Nyholm\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

class ContentLengthMiddlewareTest extends TestCase
{
    public function testMiddleware()
    {
        $request = new Request('POST', 'http://foo.com', [], 'content');
        $this->assertEmpty($request->getHeader('Content-Length'));

        /** @var RequestInterface $updatedRequest */
        $updatedRequest = null;
        $middleware = new ContentLengthMiddleware();
        $middleware->handleRequest($request, function(RequestInterface $request) use (&$updatedRequest) {
            $updatedRequest = $request;
        });

        $this->assertEquals('7', $updatedRequest->getHeaderLine('Content-Length'));
    }
}
